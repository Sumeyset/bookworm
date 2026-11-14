<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\Review;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

/**
 * The reading feed: posts, shared quotes and reviews, newest first.
 * "Following" shows you and the readers you follow; "Everyone" shows all readers.
 */
class FeedController extends Controller
{
    private const PAGE = 15;

    public function index(Request $request): View
    {
        $user = $request->user();
        $scope = $request->query('scope') === 'everyone' ? 'everyone' : 'following';
        $before = $request->filled('before') ? Carbon::parse((string) $request->query('before')) : null;

        $authorIds = $scope === 'following'
            ? $user->following()->pluck('users.id')->push($user->id)
            : null;

        $limit = fn ($query) => $query
            ->when($authorIds, fn ($q) => $q->whereIn('user_id', $authorIds))
            ->when($before, fn ($q) => $q->where('created_at', '<', $before))
            ->latest()
            ->take(self::PAGE);

        $posts = $limit(Post::query())
            ->with(['user', 'book'])
            ->withReactions($user)
            ->get();

        $reviews = $limit(Review::query())
            ->with(['user', 'book'])
            ->withReactions($user)
            ->get();

        $items = $posts->concat($reviews)
            ->sortByDesc(fn ($item) => $item->created_at->getTimestamp() * 10 + ($item instanceof Post ? 1 : 0))
            ->take(self::PAGE)
            ->values();

        $nextBefore = ($posts->count() + $reviews->count()) > self::PAGE && $items->isNotEmpty()
            ? $items->last()->created_at->toIso8601String()
            : null;

        return view('feed.index', [
            'items' => $items,
            'scope' => $scope,
            'nextBefore' => $nextBefore,
            'following' => $user->following()->withCount('reviews')->orderBy('name')->take(8)->get(),
            'shelfBooks' => $user->shelfItems()->with('book:id,title,author_name')->latest('updated_at')->get()->pluck('book')->filter()->unique('id')->values(),
            'quoteCards' => $user->quoteCards()->with('highlight.bookPdf.book:id,title,author_name')->latest()->get(),
        ]);
    }
}
