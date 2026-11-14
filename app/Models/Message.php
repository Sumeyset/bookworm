<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    /** What the inbox shows for this message. */
    public function preview(User $viewer): string
    {
        $prefix = $this->user_id === $viewer->id ? 'You: ' : '';
        if ($this->post_id && blank($this->body)) {
            return $prefix . 'Sent a post';
        }

        return $prefix . str($this->body)->squish()->limit(60);
    }
}
