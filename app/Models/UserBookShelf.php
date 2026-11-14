<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBookShelf extends Model
{
    public const STATUSES = [
        'want_to_read' => 'Want to read',
        'reading' => 'Currently reading',
        'read' => 'Read',
    ];

    public const MAX_BOOKS_PER_SHELF = 3;

    /** The tree can grow up to this many branches (slots 0..MAX_SHELVES-1). */
    public const MAX_SHELVES = 40;

    protected $fillable = [
        'user_id',
        'book_id',
        'status',
        'shelf_slot',
        'shelf_order',
        'shelf_name',
        'note',
        'current_page',
        'total_pages',
        'started_at',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'started_at' => 'datetime',
            'shelf_slot' => 'integer',
            'shelf_order' => 'integer',
            'current_page' => 'integer',
            'total_pages' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        // A newly shelved book goes onto the first branch with room, so it is
        // always visible on the tree right away.
        static::creating(function (UserBookShelf $item): void {
            if ($item->shelf_slot === null && $item->user_id) {
                [$slot, $order] = static::nextOpenPosition($item->user_id);
                $item->shelf_slot = $slot;
                $item->shelf_order = $order;
            }
        });

        static::saving(function (UserBookShelf $item): void {
            if ($item->isDirty('status') && $item->status === 'reading' && $item->started_at === null) {
                $item->started_at = now();
            }
        });

        static::saved(function (UserBookShelf $item): void {
            // Moving the bookmark forward or finishing a book counts as a reading day.
            $pagesForward = max(0, (int) $item->current_page - (int) $item->getOriginal('current_page'));
            $finished = $item->wasChanged('status') && $item->status === 'read';
            if (($item->wasChanged('current_page') && $pagesForward > 0) || $finished) {
                \App\Models\ReadingSession::log($item->user_id, $item->book_id, $item->wasChanged('current_page') ? $pagesForward : 0);
            }

            if ($finished) {
                static::notifyGoalMilestone($item);
            }

            // Tell followers when a book is started or finished.
            $statusMoved = $item->wasRecentlyCreated || $item->wasChanged('status');
            if ($statusMoved && in_array($item->status, ['reading', 'read'], true) && $item->user && $item->book) {
                $item->status === 'read'
                    ? \App\Support\FriendActivity::finishedReading($item->user, $item->book)
                    : \App\Support\FriendActivity::startedReading($item->user, $item->book);
            }
        });
    }

    /**
     * @return array{0: int, 1: int} [slot, order]
     */
    public static function nextOpenPosition(int $userId, ?int $ignoreId = null): array
    {
        $counts = static::query()
            ->where('user_id', $userId)
            ->when($ignoreId, fn ($query) => $query->whereKeyNot($ignoreId))
            ->selectRaw('shelf_slot, COUNT(*) as aggregate')
            ->groupBy('shelf_slot')
            ->pluck('aggregate', 'shelf_slot');

        for ($slot = 0; $slot < self::MAX_SHELVES; $slot++) {
            $used = (int) ($counts[$slot] ?? 0);
            if ($used < self::MAX_BOOKS_PER_SHELF) {
                return [$slot, $used];
            }
        }

        return [self::MAX_SHELVES - 1, self::MAX_BOOKS_PER_SHELF];
    }

    /** Cheer the reader on at 50%, 80% and 100% of their yearly goal. */
    private static function notifyGoalMilestone(UserBookShelf $item): void
    {
        $user = $item->user;
        $goal = max(1, (int) ($user->yearly_reading_goal ?: 12));
        $finished = static::query()
            ->where('user_id', $user->id)
            ->where('status', 'read')
            ->whereYear('read_at', now()->year)
            ->count();

        foreach ([100 => 'You reached your reading goal for ' . now()->year . '! 🎉', 80 => '80% of your yearly goal — the finish line is in sight.', 50 => 'Halfway to your yearly reading goal!'] as $percent => $message) {
            if ($finished === (int) ceil($goal * $percent / 100)) {
                \App\Notifications\BookwormNotification::send($user, 'goal', $message . " ({$finished} of {$goal} books)", route('users.show', $user->username));
                break;
            }
        }
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function markAsRead(): void
    {
        $readAt = $this->status === 'read' ? ($this->read_at ?? now()) : now();

        $this->forceFill([
            'status' => 'read',
            'read_at' => $readAt,
        ])->save();
    }

    public function progressPercent(): ?int
    {
        if (! $this->total_pages) {
            return null;
        }

        if ($this->status === 'read') {
            return 100;
        }

        return min(100, (int) round(($this->current_page ?? 0) / $this->total_pages * 100));
    }

    /**
     * Data for the book popup on the tree. Private details (notes, progress)
     * are only included for the shelf's owner.
     */
    public function treePayload(bool $private): array
    {
        $book = $this->book;
        $review = $book->relationLoaded('reviews')
            ? $book->reviews->firstWhere('user_id', $this->user_id)
            : $book->reviews()->where('user_id', $this->user_id)->first();
        $pdf = $book->relationLoaded('bookPdfs')
            ? $book->bookPdfs->firstWhere('user_id', $this->user_id)
            : null;

        $payload = [
            'book_id' => $book->id,
            'title' => $book->title,
            'author' => $book->author_name,
            'year' => $book->first_publish_year,
            'cover' => $book->cover_src,
            'url' => route('books.show', $book),
            'status' => $this->status,
            'status_label' => self::STATUSES[$this->status] ?? 'On shelf',
            'slot' => (int) $this->shelf_slot,
            'rating' => $review ? (float) $review->rating : null,
            'review' => $review?->body,
            'read_at' => $this->read_at?->format('M j, Y'),
        ];

        if (! $private) {
            return $payload;
        }

        return $payload + [
            'note' => $this->note,
            'current_page' => $this->current_page,
            'total_pages' => $this->total_pages,
            'progress' => $this->progressPercent(),
            'started_at' => $this->started_at?->format('M j, Y'),
            'added_at' => $this->created_at?->format('M j, Y'),
            'bookmark_moved' => $this->updated_at?->diffForHumans(),
            'pdf_url' => $pdf ? route('books.pdfs.reader', [$book, $pdf]) : null,
            'save_url' => route('shelf.entry.update', $book),
            'move_url' => route('shelf.entry.move', $book),
            'remove_url' => route('shelves.books.remove', [(int) $this->shelf_slot, $book]),
        ];
    }
}
