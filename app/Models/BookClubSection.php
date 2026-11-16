<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BookClubSection extends Model
{
    protected $fillable = ['book_club_round_id', 'number', 'title', 'from_page', 'to_page', 'due_on'];

    protected function casts(): array
    {
        return ['due_on' => 'date'];
    }

    public function round(): BelongsTo
    {
        return $this->belongsTo(BookClubRound::class, 'book_club_round_id');
    }

    public function posts(): HasMany
    {
        return $this->hasMany(BookClubPost::class)->oldest();
    }
}
