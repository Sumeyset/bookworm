<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBadge extends Model
{
    protected $fillable = ['user_id', 'key', 'name', 'emoji', 'description'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
