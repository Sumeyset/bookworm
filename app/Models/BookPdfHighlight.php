<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BookPdfHighlight extends Model
{
    protected $fillable = [
        'user_id',
        'page_number',
        'text',
        'rectangles',
    ];

    protected function casts(): array
    {
        return [
            'rectangles' => 'array',
        ];
    }

    public function bookPdf(): BelongsTo
    {
        return $this->belongsTo(BookPdf::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quoteCard(): HasOne
    {
        return $this->hasOne(QuoteCard::class);
    }
}
