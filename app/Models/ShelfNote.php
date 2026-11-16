<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ShelfNote extends Model
{
    public const COLORS = ['yellow', 'pink', 'mint', 'blue'];

    protected $fillable = ['user_book_shelf_id', 'author_id', 'body', 'color'];

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function shelfItem(): BelongsTo
    {
        return $this->belongsTo(UserBookShelf::class, 'user_book_shelf_id');
    }
}
