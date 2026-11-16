<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BookClubPost extends Model
{
    protected $fillable = ['book_club_section_id', 'user_id', 'body'];

    public function section(): BelongsTo
    {
        return $this->belongsTo(BookClubSection::class, 'book_club_section_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
