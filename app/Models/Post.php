<?php

namespace App\Models;

use App\Models\Concerns\HasReactions;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Post extends Model
{
    use HasReactions;

    public const TYPES = ['post', 'quote'];

    /** Card designs a quote (or a thought) can be shown on. Keep in sync with resources/css/quote-cards.css. */
    public const CARD_STYLES = ['wine', 'paper', 'midnight', 'sage', 'sunset', 'noir', 'blush', 'ocean'];

    public const CARD_FONTS = ['serif', 'classic', 'hand', 'sans', 'mono'];

    /** Photo frame shapes and colour filters. Keep in sync with resources/css/posts.css. */
    public const PHOTO_RATIOS = ['square', 'portrait', 'landscape'];

    public const PHOTO_FILTERS = ['normal', 'warm', 'cool', 'vintage', 'fade', 'golden', 'rose', 'vivid', 'mono', 'noir'];

    public const MAX_PHOTOS = 10;

    protected $fillable = ['user_id', 'type', 'body', 'quote_text', 'quote_page', 'book_id', 'quote_card_id', 'card_style', 'card_font', 'photo_ratio'];

    protected static function booted(): void
    {
        // Remove the photo files too (the rows go with the post).
        static::deleting(function (Post $post): void {
            $post->photos()->get()->each->delete();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function photos(): HasMany
    {
        return $this->hasMany(PostPhoto::class)->orderBy('position');
    }

    public function hasPhotos(): bool
    {
        return $this->relationLoaded('photos') ? $this->photos->isNotEmpty() : $this->photos()->exists();
    }

    public function photoRatio(): string
    {
        return in_array($this->photo_ratio, self::PHOTO_RATIOS, true) ? $this->photo_ratio : 'square';
    }

    public function quoteCard(): BelongsTo
    {
        return $this->belongsTo(QuoteCard::class);
    }

    public function isQuote(): bool
    {
        return $this->type === 'quote';
    }

    /** Quotes always sit on a card; a thought only when its author picked a design (and has no photos). */
    public function hasCard(): bool
    {
        return $this->isQuote() || ($this->card_style !== null && ! $this->hasPhotos());
    }

    public function cardStyle(): string
    {
        return in_array($this->card_style, self::CARD_STYLES, true) ? $this->card_style : 'wine';
    }

    public function cardFont(): string
    {
        return in_array($this->card_font, self::CARD_FONTS, true) ? $this->card_font : ($this->isQuote() ? 'serif' : 'sans');
    }
}
