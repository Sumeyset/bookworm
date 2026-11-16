<?php

namespace App\Support;

use App\Models\PushSubscription;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Minishlink\WebPush\Subscription;
use Minishlink\WebPush\WebPush;

/**
 * Push notifications to readers' phones and browsers (only those that turned
 * them on). Sent after the response, so pages never wait for it.
 */
class Push
{
    /** Turned off in tests and bulk work. */
    public static bool $enabled = true;

    public static function configured(): bool
    {
        return filled(config('webpush.public_key')) && filled(config('webpush.private_key'));
    }

    public static function toUser(User $user, string $title, string $body, string $url, ?string $tag = null): void
    {
        if (! self::$enabled || ! self::configured()) {
            return;
        }

        $payload = json_encode(['title' => $title, 'body' => mb_strimwidth($body, 0, 180, '…'), 'url' => $url, 'tag' => $tag]);
        $userId = $user->id;

        dispatch(function () use ($userId, $payload) {
            Push::deliver($userId, $payload);
        })->afterResponse();
    }

    public static function deliver(int $userId, string $payload): void
    {
        $subscriptions = PushSubscription::where('user_id', $userId)->get();
        if ($subscriptions->isEmpty()) {
            return;
        }

        try {
            $webPush = new WebPush(['VAPID' => [
                'subject' => config('webpush.subject'),
                'publicKey' => config('webpush.public_key'),
                'privateKey' => config('webpush.private_key'),
            ]], ['TTL' => 3600], 8);

            foreach ($subscriptions as $sub) {
                $webPush->queueNotification(Subscription::create([
                    'endpoint' => $sub->endpoint,
                    'publicKey' => $sub->public_key,
                    'authToken' => $sub->auth_token,
                    'contentEncoding' => $sub->content_encoding,
                ]), $payload);
            }

            foreach ($webPush->flush() as $report) {
                // The phone unsubscribed or the browser forgot us: stop sending there.
                if ($report->isSubscriptionExpired()) {
                    PushSubscription::where('endpoint_hash', hash('sha256', $report->getEndpoint()))->delete();
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Push notification failed: ' . $e->getMessage());
        }
    }
}
