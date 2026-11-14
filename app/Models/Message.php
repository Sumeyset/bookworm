<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Message extends Model
{
    protected $fillable = ['conversation_id', 'user_id', 'body', 'post_id'];

    public function conversation(): BelongsTo
    {
        return $this->belongsTo(Conversation::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(MessageAttachment::class);
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /** What the inbox shows for this message. */
    public function preview(User $viewer): string
    {
        $prefix = $this->user_id === $viewer->id ? 'You: ' : '';
        if (blank($this->body)) {
            $types = $this->relationLoaded('attachments') ? $this->attachments->pluck('type') : $this->attachments()->pluck('type');
            if ($types->contains('audio')) {
                return $prefix . 'Sent a voice message';
            }
            if ($types->contains('image')) {
                return $prefix . ($types->count() > 1 ? 'Sent ' . $types->count() . ' photos' : 'Sent a photo');
            }
            if ($types->contains('file')) {
                return $prefix . 'Sent a file';
            }
            if ($this->post_id) {
                return $prefix . 'Sent a post';
            }
        }

        return $prefix . str($this->body)->squish()->limit(60);
    }
}
