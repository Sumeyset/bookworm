<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Support\Pro;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use RuntimeException;

class ProController extends Controller
{
    public function show(Request $request): View
    {
        return view('pro.show', [
            'pro' => Pro::active($request->user()),
            'configured' => Pro::configured(),
            'hasYearly' => filled(config('services.stripe.price_yearly')),
            'welcome' => $request->boolean('welcome'),
        ]);
    }

    public function checkout(Request $request): RedirectResponse
    {
        abort_unless(Pro::configured(), 404);
        $validated = $request->validate(['plan' => ['required', Rule::in(['monthly', 'yearly'])]]);

        try {
            return redirect()->away(Pro::checkoutUrl($request->user(), $validated['plan']));
        } catch (RuntimeException $e) {
            return back()->withErrors(['pro' => $e->getMessage()]);
        }
    }

    public function portal(Request $request): RedirectResponse
    {
        abort_unless(Pro::configured() && $request->user()->stripe_customer_id, 404);

        try {
            return redirect()->away(Pro::portalUrl($request->user()));
        } catch (RuntimeException $e) {
            return back()->withErrors(['pro' => $e->getMessage()]);
        }
    }

    /** Stripe tells us about payments here. Signed; each event handled once. */
    public function webhook(Request $request): Response
    {
        $payload = $request->getContent();
        if (! Pro::verify($payload, $request->header('Stripe-Signature'))) {
            return response('Bad signature', 400);
        }
        $event = json_decode($payload, true) ?: [];
        if (empty($event['id']) || ! DB::table('stripe_events')->insertOrIgnore(['id' => $event['id'], 'type' => $event['type'] ?? '', 'created_at' => now()])) {
            return response('Already handled', 200);
        }

        $object = $event['data']['object'] ?? [];
        match ($event['type'] ?? '') {
            'checkout.session.completed' => $this->checkoutDone($object),
            'customer.subscription.created', 'customer.subscription.updated', 'customer.subscription.deleted' => $this->subscriptionChanged($object),
            default => null,
        };

        return response('ok', 200);
    }

    private function checkoutDone(array $session): void
    {
        $user = User::find((int) ($session['client_reference_id'] ?? 0));
        if (! $user) {
            Log::warning('Stripe checkout for an unknown reader', ['session' => $session['id'] ?? null]);

            return;
        }
        $user->forceFill([
            'stripe_customer_id' => $session['customer'] ?? $user->stripe_customer_id,
            'stripe_subscription_id' => $session['subscription'] ?? $user->stripe_subscription_id,
            // Until Stripe sends the subscription's period, give a month.
            'pro_until' => max($user->pro_until ?? now(), now()->addMonth()),
        ])->save();
        \App\Notifications\BookwormNotification::send($user, 'badge', '✦ Welcome to Bookworm Pro! Thank you for supporting Bookworm.', route('pro.show'));
    }

    private function subscriptionChanged(array $subscription): void
    {
        $user = User::where('stripe_customer_id', $subscription['customer'] ?? '')->first()
            ?? User::find((int) ($subscription['metadata']['user_id'] ?? 0));
        if (! $user) {
            return;
        }
        $active = in_array($subscription['status'] ?? '', ['active', 'trialing', 'past_due'], true);
        $end = $subscription['current_period_end'] ?? $subscription['items']['data'][0]['current_period_end'] ?? null;
        $user->forceFill([
            'stripe_customer_id' => $subscription['customer'] ?? $user->stripe_customer_id,
            'stripe_subscription_id' => $subscription['id'] ?? $user->stripe_subscription_id,
            'pro_until' => $active && $end ? Carbon::createFromTimestamp($end) : ($active ? now()->addMonth() : now()),
        ])->save();
    }
}
