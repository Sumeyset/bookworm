<?php

namespace App\Notifications;

use App\Models\User;
use Illuminate\Notifications\Notification;

/**
 * One notification type for everything in the bell menu: new followers,
 * likes, comments, buddy reads, goal milestones and reviews of books you want to read.
 */
class BookwormNotification extends Notification
{
    public function __construct(
        public string $kind,
        public string $message,
        public string $url,
        public ?User $actor = null,
    ) {
    }

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'kind' => $this->kind,
            'message' => $this->message,
            'url' => $this->url,
            'actor_name' => $this->actor?->name,
            'actor_username' => $this->actor?->username,
            'actor_photo' => $this->actor?->profile_photo_url,
        ];
    }

    /** Send unless the recipient is the one who did the thing. */
    public static function send(?User $to, string $kind, string $message, string $url, ?User $actor = null): void
    {
        if (! $to || ($actor && $to->is($actor))) {
            return;
        }

        $to->notify(new self($kind, $message, $url, $actor));
    }
}
