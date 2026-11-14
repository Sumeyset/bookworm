<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\User;
use App\Models\UserBookShelf;
use App\Support\BookTree;
use Illuminate\Http\Request;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(Request $request): View
    {
        $query = trim((string) $request->query('q', ''));

        $readersQuery = User::query()
            ->where('id', '!=', $request->user()->id);

        if ($query === '') {
            $readersQuery->whereKey([]);
        } else {
            $readersQuery->where(function ($users) use ($query) {
                $users->where('name', 'like', '%' . $query . '%')
                    ->orWhere('username', 'like', '%' . $query . '%');
            });
        }

        $readers = $readersQuery
            ->withCount(['followers', 'reviews'])
            ->orderBy('name')
            ->paginate(12)
            ->withQueryString();

        $following = $request->user()->following()
            ->withCount(['followers', 'reviews'])
            ->orderBy('name')
            ->take(9)
            ->get();

        return view('users.index', compact('query', 'readers', 'following'));
    }

    public function show(Request $request, User $user): View
    {
        $user->loadCount(['followers', 'following', 'reviews']);

        // Older accounts rated books before shelves existed: give each of
        // those books a place on the tree (once) so nothing is missing.
        if ($user->reviews_count > 0 && ! $user->shelfItems()->exists()) {
            $user->reviews()->orderBy('created_at')->get()->each(
                fn ($review) => $user->shelfItems()->create([
                    'book_id' => $review->book_id,
                    'status' => 'read',
                    'read_at' => $review->updated_at,
                ]),
            );
        }

        $isOwnProfile = $request->user()->id === $user->id;

        $isFollowing = $request->user()
            ->following()
            ->where('users.id', $user->id)
            ->exists();

        $requestedStatus = (string) $request->query('status', '');
        $status = array_key_exists($requestedStatus, UserBookShelf::STATUSES) ? $requestedStatus : '';
        $query = trim((string) $request->query('q', ''));
        $requestedSort = (string) $request->query('sort', 'recent');
        $sort = in_array($requestedSort, ['recent', 'added', 'title'], true) ? $requestedSort : 'recent';

        $shelfRelation = $user->shelfItems();
        $hasShelfItems = (clone $shelfRelation)->exists();
        $shelfStatusCounts = (clone $shelfRelation)
            ->when(! $isOwnProfile, fn ($items) => $items
                ->where('status', 'read')
                ->whereHas('book.reviews', fn ($reviews) => $reviews->where('user_id', $user->id)))
            ->selectRaw('status, COUNT(*) as aggregate')
            ->groupBy('status')
            ->pluck('aggregate', 'status');

        $shelfQuery = $shelfRelation
            ->with([
                'book.bookPdfs' => fn ($pdfs) => $pdfs->where('user_id', $user->id),
                'book.reviews' => fn ($reviews) => $reviews->where('user_id', $user->id),
            ])
            ->when(! $isOwnProfile, fn ($items) => $items
                ->where('status', 'read')
                ->whereHas('book.reviews', fn ($reviews) => $reviews->where('user_id', $user->id)))
            ->when($status !== '', fn ($items) => $items->where('status', $status))
            ->when($query !== '', fn ($items) => $items->whereHas('book', function ($books) use ($query) {
                $books->where('title', 'like', '%' . $query . '%')
                    ->orWhere('author_name', 'like', '%' . $query . '%');
            }))
            ->when($sort === 'title', fn ($items) => $items->orderBy(
                Book::query()->select('title')->whereColumn('books.id', 'user_book_shelves.book_id'),
            ))
            ->when($sort === 'added', fn ($items) => $items->oldest('created_at'))
            ->when($sort === 'recent', fn ($items) => $items->latest('updated_at'));

        $bookshelf = $shelfQuery->paginate(12)->withQueryString();
        foreach ($bookshelf as $shelfItem) {
            $review = $shelfItem->book->reviews->first();
            $shelfItem->setAttribute('rating', $review?->rating);
            $shelfItem->setAttribute('review_body', $review?->body);
        }

        if (! $hasShelfItems) {
            $legacyReviews = $user->reviews()
                ->with([
                    'book',
                    'book.bookPdfs' => fn ($pdfs) => $pdfs->where('user_id', $user->id),
                ])
                ->when($status !== '' && $status !== 'read', fn ($reviews) => $reviews->whereRaw('1 = 0'))
                ->when($query !== '', fn ($reviews) => $reviews->whereHas('book', function ($books) use ($query) {
                    $books->where('title', 'like', '%' . $query . '%')
                        ->orWhere('author_name', 'like', '%' . $query . '%');
                }))
                ->when($sort === 'title', fn ($reviews) => $reviews->orderBy(
                    Book::query()->select('title')->whereColumn('books.id', 'reviews.book_id'),
                ))
                ->when($sort === 'added', fn ($reviews) => $reviews->oldest('created_at'))
                ->when($sort === 'recent', fn ($reviews) => $reviews->latest('updated_at'))
                ->paginate(12)
                ->withQueryString();

            $legacyReviews->setCollection($legacyReviews->getCollection()->map(function ($review): UserBookShelf {
                $shelfItem = new UserBookShelf([
                    'book_id' => $review->book_id,
                    'status' => 'read',
                    'read_at' => $review->updated_at,
                ]);
                $shelfItem->setRelation('book', $review->book);
                $shelfItem->setAttribute('rating', $review->rating);
                $shelfItem->setAttribute('review_body', $review->body);
                $shelfItem->setAttribute('created_at', $review->created_at);
                $shelfItem->setAttribute('updated_at', $review->updated_at);

                return $shelfItem;
            }));

            $bookshelf = $legacyReviews;
            $shelfStatusCounts = $shelfStatusCounts->put('read', $user->reviews()->count());
        }

        // One query for every book on the tree; stats are computed from it
        // instead of running a separate query for each number.
        $allUserShelfItems = $user->shelfItems()
            ->with([
                'book.bookPdfs' => fn ($pdfs) => $pdfs->where('user_id', $user->id)->latest(),
                'book.reviews' => fn ($reviews) => $reviews->where('user_id', $user->id),
            ])
            ->get();

        $readThisYear = $allUserShelfItems->filter(
            fn ($item) => $item->status === 'read' && $item->read_at?->year === now()->year,
        );
        $monthlyReads = $readThisYear->countBy(fn ($item) => (int) $item->read_at->format('n'));

        $treeItems = $isOwnProfile
            ? $allUserShelfItems
            : $allUserShelfItems->filter(fn ($item) => $item->status === 'read' && $item->book->reviews->isNotEmpty());
        $treeShelves = BookTree::build($user, $treeItems, $isOwnProfile);

        return view('users.show', [
            'profile' => $user,
            'bookshelf' => $bookshelf,
            'shelfStatuses' => UserBookShelf::STATUSES,
            'shelfStatus' => $status,
            'shelfQuery' => $query,
            'shelfSort' => $sort,
            'shelfStatusCounts' => $shelfStatusCounts->put('all', $shelfStatusCounts->sum()),
            'isOwnProfile' => $isOwnProfile,
            'isFollowing' => $isFollowing,
            'treeShelves' => $treeShelves,
            'treeBookCount' => $treeItems->count(),
            'readingStats' => [
                'books_this_year' => $readThisYear->count(),
                'currently_reading' => $allUserShelfItems->where('status', 'reading')->count(),
                'pages_read' => $readThisYear->sum('total_pages'),
                'monthly_reads' => collect(range(1, 12))->map(fn ($month) => $monthlyReads[$month] ?? 0),
                'yearly_goal' => $user->yearly_reading_goal ?: 12,
                'genres' => $user->preferred_genres ?? [],
            ],
        ]);
    }
}