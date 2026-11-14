<?php

namespace App\Models;

use App\Models\Concerns\HasReactions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Post extends Model
{
    use HasReactions;

    public const TYPES = ['post', 'quote'];

    protected $fillable = ['user_id', 'type', 'body', 'quote_text', 'quote_page', 'book_id', 'quote_card_id'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function quoteCard(): BelongsTo
    {
        return $this->belongsTo(QuoteCard::class);
    }

    public function isQuote(): bool
    {
        return $this->type === 'quote';
    }
}
