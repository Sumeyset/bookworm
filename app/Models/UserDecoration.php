<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserDecoration extends Model
{
    protected $fillable = ['user_id', 'item', 'enabled', 'gifted_by'];

    protected function casts(): array
    {
        return ['enabled' => 'boolean'];
    }

    public function giver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'gifted_by');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
