<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookClubRound extends Model
{
    protected $fillable = ['book_club_id', 'book_id', 'starts_on', 'finished_at'];

    protected function casts(): array
    {
        return ['starts_on' => 'date', 'finished_at' => 'datetime'];
    }

    public function club(): BelongsTo
    {
        return $this->belongsTo(BookClub::class, 'book_club_id');
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function sections(): HasMany
    {
        return $this->hasMany(BookClubSection::class)->orderBy('number');
    }
}
