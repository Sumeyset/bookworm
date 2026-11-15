<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AcornEvent extends Model
{
    protected $fillable = ['user_id', 'amount', 'reason', 'key', 'note'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
