<?php

namespace App\Support;

use App\Models\Book;
use App\Models\Review;
use App\Models\User;
use App\Notifications\BookwormNotification;

/**
 * Tells a reader's followers what they are reading: started, finished, rated
 * or reviewed a book. These go to notifications, never to the feed.
 */
class FriendActivity
{
    /** Turned on during bulk work (imports, back-fills) so followers aren't flooded. */
    public static bool $quiet = false;

    public static function startedReading(User $reader, Book $book): void
    {
        self::tell($reader, $book, 'reading', $reader->name . ' started reading “' . $book->title . '”.');
    }

    public static function finishedReading(User $reader, Book $book): void
    {
        // A rating or review sent a moment ago already says they read it.
        $justRated = fn (User $follower) => self::alreadyTold($follower, 'reviewed', $reader, $book, 10)
            || self::alreadyTold($follower, 'rated', $reader, $book, 10);

        self::tell($reader, $book, 'finished', $reader->name . ' finished “' . $book->title . '”.', $justRated);
    }

    public static function rated(Review $review): void
    {
        self::tell($review->user, $review->book, 'rated', $review->user->name . ' rated “' . $review->book->title . '” ' . self::stars($review) . '★.');
    }

    public static function reviewed(Review $review): void
    {
        $excerpt = str($review->body)->squish()->limit(80);

        self::tell($review->user, $review->book, 'reviewed', $review->user->name . ' reviewed “' . $review->book->title . '” (' . self::stars($review) . '★): “' . $excerpt . '”');
    }

    private static function stars(Review $review): string
    {
        $rating = (float) $review->rating;

        return $rating == floor($rating) ? (string) (int) $rating : number_format($rating, 1);
    }

    private static function tell(User $reader, Book $book, string $kind, string $message, ?callable $skip = null): void
    {
        if (self::$quiet) {
            return;
        }

        $reader->followers()
            ->with(['shelfItems' => fn ($items) => $items->where('book_id', $book->id)])
            ->get()
            ->each(function (User $follower) use ($reader, $book, $kind, $message, $skip) {
                if (self::alreadyTold($follower, $kind, $reader, $book) || ($skip && $skip($follower))) {
                    return;
                }

                $wantsIt = $follower->shelfItems->first()?->status === 'want_to_read';
                $follower->notify(new BookwormNotification(
                    $kind,
                    $message . ($wantsIt ? ' It’s on your Want to read list.' : ''),
                    route('books.show', $book),
                    $reader,
                    self::key($kind, $reader, $book),
                ));
            });
    }

    private static function key(string $kind, User $reader, Book $book): string
    {
        return $kind . ':' . $reader->id . ':' . $book->id;
    }

    private static function alreadyTold(User $follower, string $kind, User $reader, Book $book, ?int $withinMinutes = null): bool
    {
        return $follower->notifications()
            ->where('data->key', self::key($kind, $reader, $book))
            ->when($withinMinutes, fn ($query) => $query->where('created_at', '>=', now()->subMinutes($withinMinutes)))
            ->exists();
    }
}
