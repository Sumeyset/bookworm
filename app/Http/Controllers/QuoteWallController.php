<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Every quote readers have shared, plus your own saved highlights. */
class QuoteWallController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $scope = $request->query('scope') === 'mine' ? 'mine' : 'everyone';
        $search = trim((string) $request->query('q', ''));

        $quotes = Post::query()
            ->where('type', 'quote')
            ->when($scope === 'mine', fn ($q) => $q->where('user_id', $user->id))
            ->when($search !== '', fn ($q) => $q->where(function ($q) use ($search) {
                $q->where('quote_text', 'like', '%' . $search . '%')
                    ->orWhereHas('book', fn ($books) => $books->where('title', 'like', '%' . $search . '%')->orWhere('author_name', 'like', '%' . $search . '%'));
            }))
            ->with(['user', 'book'])
            ->withReactions($user)
            ->latest()
            ->paginate(18)
            ->withQueryString();

        // Saved highlights you haven't posted yet.
        $unposted = $user->quoteCards()
            ->whereDoesntHave('posts')
            ->with('highlight.bookPdf.book')
            ->latest()
            ->get();

        return view('quotes.index', compact('quotes', 'scope', 'search', 'unposted'));
    }
}
