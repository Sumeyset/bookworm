<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\QuoteCard;
use App\Models\UserBookShelf;
use App\Services\OpenLibraryService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\View\View;

class BookController extends Controller
{
    public function index(Request $request, OpenLibraryService $openLibrary): View
    {
        $query = trim((string) $request->query('q', ''));
        $results = [];
        $error = null;

        if ($query !== '') {
            try {
                $results = $openLibrary->search($query);

                // One upsert instead of a query pair per result (~40 queries).
                $rows = collect($results)
                    ->filter(fn ($result) => $result['ol_id'] !== '')
                    ->unique('ol_id')
                    ->map(fn ($result) => [
                        'ol_id' => $result['ol_id'],
                        'title' => mb_substr($result['title'], 0, 255),
                        'author_name' => $result['author_name'] ? mb_substr($result['author_name'], 0, 255) : null,
                        'cover_url' => $result['cover_url'],
                        'first_publish_year' => $result['first_publish_year'],
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                    ->values()
                    ->all();

                if ($rows !== []) {
                    Book::upsert($rows, ['ol_id'], ['title', 'author_name', 'cover_url', 'first_publish_year', 'updated_at']);
                }

                $books = Book::query()
                    ->with(['reviews' => fn ($reviews) => $reviews->where('user_id', $request->user()->id)])
                    ->whereIn('ol_id', array_column($results, 'ol_id'))
                    ->get()
                    ->keyBy('ol_id');

                $results = array_map(function ($result) use ($books) {
                    $savedBook = $books->get($result['ol_id']);
                    $result['database_id'] = $savedBook?->id;
                    $result['rating'] = $savedBook?->reviews->first()?->rating ?? 0;

                    return $result;
                }, $results);
            } catch (ConnectionException|RequestException $exception) {
                report($exception);
                $error = 'Open Library could not complete the search. Please try again shortly.';
            }
        }

        $recommendations = $query === '' ? \App\Support\Recommender::for($request->user()) : [];

        return view('books.index', compact('query', 'results', 'error', 'recommendations'));
    }

    public function show(Request $request, Book $book): View
    {
        $stats = $book->reviews()
            ->selectRaw('COUNT(*) as count, AVG(rating) as average')
            ->first();

        return view('books.show', [
            'book' => $book,
            'average' => $stats->average,
            'count' => (int) $stats->count,
            'myReview' => $book->reviews()->where('user_id', auth()->id())->first(),
            'myShelfItem' => $request->user()->shelfItems()->where('book_id', $book->id)->first(),
            'shelfStatuses' => UserBookShelf::STATUSES,
            'myPdfs' => $request->user()->bookPdfs()->where('book_id', $book->id)->latest()->get(),
            'quotes' => QuoteCard::query()
                ->whereHas('highlight.bookPdf', fn ($pdfs) => $pdfs->where('book_id', $book->id))
                ->where(fn ($quotes) => $quotes->where('user_id', $request->user()->id)->orWhereNotNull('shared_at'))
                ->with(['highlight', 'user'])
                ->latest()
                ->paginate(12, ['*'], 'quotes_page'),
            'reviews' => $book->reviews()->with('user')->latest()->paginate(10, ['*'], 'reviews_page'),
        ]);
    }
}
