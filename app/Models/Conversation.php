<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/** A private, two-person chat. */
class Conversation extends Model
{
    protected $fillable = ['last_message_at'];

    protected function casts(): array
    {
        return ['last_message_at' => 'datetime'];
    }

    protected static function booted(): void
    {
        // Attachment files live on disk; remove them with the conversation.
        static::deleting(function (Conversation $conversation): void {
            MessageAttachment::deleteFilesFor(MessageAttachment::whereIn('message_id', $conversation->messages()->select('id')));
        });
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot(['last_read_at', 'accepted_at'])->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    public function latestMessage(): HasOne
    {
        return $this->hasOne(Message::class)->latestOfMany();
    }

    /** The conversation between two readers, created the first time they talk. */
    public static function between(User $me, User $them): self
    {
        $existing = static::query()
            ->whereHas('users', fn ($users) => $users->where('users.id', $me->id))
            ->whereHas('users', fn ($users) => $users->where('users.id', $them->id))
            ->first();

        if ($existing) {
            return $existing;
        }

        $conversation = static::create();
        $conversation->users()->attach($me->id, ['accepted_at' => now(), 'last_read_at' => now()]);
        // If they follow me, it goes straight to their inbox; otherwise it's a request.
        $theyFollowMe = $them->following()->where('users.id', $me->id)->exists();
        $conversation->users()->attach($them->id, ['accepted_at' => $theyFollowMe ? now() : null]);

        return $conversation;
    }

    public function other(User $me): ?User
    {
        return $this->users->firstWhere('id', '!=', $me->id);
    }

    public function membership(User $user): ?object
    {
        return $this->users->firstWhere('id', $user->id)?->pivot;
    }

    public function hasMember(User $user): bool
    {
        return $this->users()->where('users.id', $user->id)->exists();
    }
}
