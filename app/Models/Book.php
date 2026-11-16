<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    protected static function booted(): void
    {
        // A new cover means new spine colours (worked out again from the cover).
        static::saving(function (Book $book): void {
            if ($book->isDirty('cover_url')) {
                $book->spine_color = null;
                $book->spine_text = null;
            }
        });
    }

    protected $fillable = [
        'ol_id',
        'isbn',
        'title',
        'author_name',
        'cover_url',
        'first_publish_year',
        'page_count',
    ];

    /** Cover URL served from our own cache (see CoverController). */
    public function getCoverSrcAttribute(): ?string
    {
        return $this->cover_url
            ? route('covers.show', ['book' => $this->id, 'v' => \App\Support\CoverCache::key($this)])
            : null;
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function shelfItems()
    {
        return $this->hasMany(UserBookShelf::class);
    }

    public function bookPdfs(): HasMany
    {
        return $this->hasMany(BookPdf::class);
    }

}
