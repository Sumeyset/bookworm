<?php

namespace App\Support;

use App\Models\User;
use Illuminate\Support\Facades\Http;
use RuntimeException;

/**
 * Bookworm Pro, paid with Stripe (Checkout + Customer Portal + webhooks).
 * Keys live only in .env; without them, Pro can still be granted by hand.
 */
class Pro
{
    public const BARNABY_PER_DAY = 150;

    public const FREEZES = 4;

    public const ACORN_BONUS = 1.25;

    public static function active(?User $user): bool
    {
        return (bool) $user?->pro_until?->isFuture();
    }

    public static function configured(): bool
    {
        return filled(config('services.stripe.secret')) && filled(config('services.stripe.price_monthly'));
    }

    /** A Stripe Checkout page for a plan; returns its URL. */
    public static function checkoutUrl(User $user, string $plan): string
    {
        $price = $plan === 'yearly' ? config('services.stripe.price_yearly') : config('services.stripe.price_monthly');
        if (! $price) {
            throw new RuntimeException('That plan isn’t available.');
        }

        $params = [
            'mode' => 'subscription',
            'line_items[0][price]' => $price,
            'line_items[0][quantity]' => 1,
            'client_reference_id' => (string) $user->id,
            'success_url' => route('pro.show') . '?welcome=1',
            'cancel_url' => route('pro.show'),
            'allow_promotion_codes' => 'true',
            'subscription_data[metadata][user_id]' => (string) $user->id,
        ];
        $params += $user->stripe_customer_id ? ['customer' => $user->stripe_customer_id] : ['customer_email' => $user->email];

        return self::stripe('checkout/sessions', $params)['url'];
    }

    /** Stripe's own page to change card, switch plan or cancel. */
    public static function portalUrl(User $user): string
    {
        return self::stripe('billing_portal/sessions', ['customer' => $user->stripe_customer_id, 'return_url' => route('pro.show')])['url'];
    }

    /** Check a webhook really came from Stripe (Stripe-Signature header, 5-minute window). */
    public static function verify(string $payload, ?string $header): bool
    {
        $secret = config('services.stripe.webhook_secret');
        if (! $secret || ! $header) {
            return false;
        }
        $timestamp = 0;
        $signatures = [];
        foreach (explode(',', $header) as $part) {
            [$key, $value] = array_pad(explode('=', trim($part), 2), 2, '');
            if ($key === 't') {
                $timestamp = (int) $value;
            } elseif ($key === 'v1') {
                $signatures[] = $value;
            }
        }
        if (! $timestamp || abs(time() - $timestamp) > 300) {
            return false;
        }
        $expected = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

        return collect($signatures)->contains(fn ($signature) => hash_equals($expected, $signature));
    }

    private static function stripe(string $path, array $params): array
    {
        $response = Http::asForm()->withToken(config('services.stripe.secret'))->timeout(15)->post('https://api.stripe.com/v1/' . $path, $params);
        if (! $response->successful()) {
            throw new RuntimeException('Stripe said: ' . ($response->json('error.message') ?? 'something went wrong.'));
        }

        return $response->json();
    }
}
