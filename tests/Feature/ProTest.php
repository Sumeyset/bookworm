<?php

use App\Models\User;
use App\Support\Pro;
use Illuminate\Support\Facades\Http;

beforeEach(function () {
    config(['services.stripe' => ['secret' => 'sk_test_x', 'webhook_secret' => 'whsec_test', 'price_monthly' => 'price_m', 'price_yearly' => 'price_y']]);
});

function stripeEvent(array $event): array
{
    $payload = json_encode($event);
    $t = time();

    return [$payload, 't=' . $t . ',v1=' . hash_hmac('sha256', $t . '.' . $payload, 'whsec_test')];
}

test('checkout sends the reader to Stripe', function () {
    $me = User::factory()->create();
    Http::fake(['api.stripe.com/v1/checkout/sessions' => Http::response(['url' => 'https://checkout.stripe.com/pay/cs_1'])]);

    $this->actingAs($me)->post(route('pro.checkout'), ['plan' => 'monthly'])->assertRedirect('https://checkout.stripe.com/pay/cs_1');
    Http::assertSent(fn ($r) => $r['line_items[0][price]'] === 'price_m' && $r['client_reference_id'] === (string) $me->id && $r->hasHeader('Authorization', 'Bearer sk_test_x'));
});

test('signed webhooks turn Pro on and off; bad signatures and repeats are ignored', function () {
    $me = User::factory()->create();
    $end = now()->addYear()->timestamp;

    [$payload, $sig] = stripeEvent(['id' => 'evt_1', 'type' => 'checkout.session.completed', 'data' => ['object' => ['client_reference_id' => (string) $me->id, 'customer' => 'cus_1', 'subscription' => 'sub_1']]]);
    $this->call('POST', route('stripe.webhook'), [], [], [], ['HTTP_STRIPE_SIGNATURE' => $sig, 'CONTENT_TYPE' => 'application/json'], $payload)->assertOk();
    expect($me->fresh()->isPro())->toBeTrue()->and($me->fresh()->stripe_customer_id)->toBe('cus_1');

    [$payload, $sig] = stripeEvent(['id' => 'evt_2', 'type' => 'customer.subscription.updated', 'data' => ['object' => ['id' => 'sub_1', 'customer' => 'cus_1', 'status' => 'active', 'current_period_end' => $end]]]);
    $this->call('POST', route('stripe.webhook'), [], [], [], ['HTTP_STRIPE_SIGNATURE' => $sig], $payload)->assertOk();
    expect($me->fresh()->pro_until->timestamp)->toBe($end);

    // Forged
    $this->call('POST', route('stripe.webhook'), [], [], [], ['HTTP_STRIPE_SIGNATURE' => 't=' . time() . ',v1=nope'], $payload)->assertStatus(400);

    [$payload, $sig] = stripeEvent(['id' => 'evt_3', 'type' => 'customer.subscription.deleted', 'data' => ['object' => ['id' => 'sub_1', 'customer' => 'cus_1', 'status' => 'canceled']]]);
    $this->call('POST', route('stripe.webhook'), [], [], [], ['HTTP_STRIPE_SIGNATURE' => $sig], $payload)->assertOk();
    expect($me->fresh()->isPro())->toBeFalse();
});

test('pro perks: more acorns, more freezes, a badge', function () {
    $me = User::factory()->create(['pro_until' => now()->addMonth()]);
    \App\Support\Acorns::award($me, 100, 'test', 'k1');
    expect($me->fresh()->acorns)->toBe(125)
        ->and(\App\Support\Streak::maxFor($me))->toBe(Pro::FREEZES);
    $this->actingAs($me)->get(route('users.show', $me->username))->assertSee('✦ PRO');
    $this->artisan('bookworm:pro', ['user' => $me->username, 'days' => 0])->assertSuccessful();
    expect($me->fresh()->isPro())->toBeFalse();
});
