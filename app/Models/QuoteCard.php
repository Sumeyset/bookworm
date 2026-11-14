<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class QuoteCard extends Model
{
    protected $fillable = [
        'user_id',
        'book_pdf_highlight_id',
        'share_token',
        'shared_at',
    ];

    protected function casts(): array
    {
        return ['shared_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function highlight(): BelongsTo
    {
        return $this->belongsTo(BookPdfHighlight::class, 'book_pdf_highlight_id');
    }
}
