<?php

namespace App\Models;

use App\Models\Concerns\HasReactions;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasReactions;

    protected $fillable = ['user_id', 'book_id', 'rating', 'body'];

    protected function casts(): array
    {
        return ['rating' => 'decimal:1'];
    }

    protected static function booted(): void
    {
        // Let followers who want to read this book know a reader they trust reviewed it.
        static::created(function (Review $review): void {
            $author = $review->user;
            $review->user->followers()
                ->whereHas('shelfItems', fn ($items) => $items->where('book_id', $review->book_id)->where('status', 'want_to_read'))
                ->get()
                ->each(fn (User $follower) => \App\Notifications\BookwormNotification::send(
                    $follower,
                    'review',
                    $author->name . ' reviewed “' . $review->book->title . '”, which is on your Want to read list.',
                    route('books.show', $review->book_id),
                    $author,
                ));
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
