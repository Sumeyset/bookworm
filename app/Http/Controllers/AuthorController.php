<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Post;
use App\Models\Review;
use App\Models\UserBookShelf;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** An author's page: their books on Bookworm, friends who read them, and quotes. */
class AuthorController extends Controller
{
    public function show(Request $request, string $name): View
    {
        $me = $request->user();
        $name = trim(urldecode($name));
        $books = Book::whereRaw('LOWER(author_name) = ?', [mb_strtolower($name)])
            ->orderByRaw('first_publish_year IS NULL')->orderBy('first_publish_year')->orderBy('title')
            ->get();
        abort_if($books->isEmpty(), 404);
        $name = $books->first()->author_name;
        $ids = $books->pluck('id');

        $ratings = Review::whereIn('book_id', $ids)->selectRaw('book_id, AVG(rating) as stars, COUNT(*) as votes')->groupBy('book_id')->get()->keyBy('book_id');
        $mine = $me->shelfItems()->whereIn('book_id', $ids)->pluck('status', 'book_id');

        $friendIds = $me->following()->pluck('users.id');
        $friends = UserBookShelf::whereIn('book_id', $ids)->whereIn('user_id', $friendIds)->whereIn('status', ['reading', 'read'])
            ->with('user')->get()->groupBy('user_id')
            ->map(fn ($items) => ['user' => $items->first()->user, 'count' => $items->count()])
            ->filter(fn ($f) => $f['user']->visibleTo($me))
            ->sortByDesc('count')->values();

        return view('authors.show', [
            'name' => $name,
            'books' => $books,
            'ratings' => $ratings,
            'mine' => $mine,
            'friends' => $friends,
            'readers' => UserBookShelf::whereIn('book_id', $ids)->where('status', 'read')->distinct()->count('user_id'),
            'quotes' => Post::where('type', 'quote')->whereIn('book_id', $ids)->whereNotIn('user_id', $me->blockedWithIds())
                ->whereHas('user', fn ($u) => $u->where('is_private', false)->orWhereIn('users.id', $friendIds->push($me->id)))
                ->with(['user', 'book'])->withCount('likes')->orderByDesc('likes_count')->latest()->take(6)->get(),
        ]);
    }
}
