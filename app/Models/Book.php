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

        // Keep a copy of a new cover on our own server straight away (after the
        // page is sent), so nobody waits for Open Library when it's first shown.
        static::saved(function (Book $book): void {
            if (! $book->cover_url || ! ($book->wasRecentlyCreated || $book->wasChanged('cover_url')) || app()->runningUnitTests()) {
                return;
            }
            $id = $book->id;
            dispatch(function () use ($id) {
                $fresh = Book::find($id);
                if ($fresh && \App\Support\CoverCache::ensure($fresh)) {
                    \App\Support\SpineColor::for($fresh);
                }
            })->afterResponse();
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

    /** The other books in this book's series, in order. */
    public function seriesBooks()
    {
        return $this->series_name
            ? static::where('series_name', $this->series_name)->orderByRaw('series_number IS NULL')->orderBy('series_number')->orderBy('title')->get()
            : collect();
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
