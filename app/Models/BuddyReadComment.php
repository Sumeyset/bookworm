<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BuddyReadComment extends Model
{
    protected $fillable = ['buddy_read_id', 'user_id', 'page', 'body'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function buddyRead(): BelongsTo
    {
        return $this->belongsTo(BuddyRead::class);
    }
}
