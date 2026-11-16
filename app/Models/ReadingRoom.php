<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ReadingRoom extends Model
{
    protected $fillable = ['host_id', 'title', 'starts_at', 'ends_at'];

    protected function casts(): array
    {
        return ['starts_at' => 'datetime', 'ends_at' => 'datetime'];
    }

    public function host(): BelongsTo
    {
        return $this->belongsTo(User::class, 'host_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'reading_room_members')->withPivot(['book_id', 'last_seen_at'])->withTimestamps();
    }

    public function messages(): HasMany
    {
        return $this->hasMany(ReadingRoomMessage::class);
    }

    public function isLive(): bool
    {
        return $this->ends_at->isFuture();
    }

    public function scopeLive(Builder $query): Builder
    {
        return $query->where('ends_at', '>', now());
    }
}
