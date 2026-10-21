<?php

namespace App\Http\Controllers;

use App\Models\Book;
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

                foreach ($results as $result) {
                    if ($result['ol_id'] !== '') {
                        Book::updateOrCreate(
                            ['ol_id' => $result['ol_id']],
                            [
                                'title' => $result['title'],
                                'author_name' => $result['author_name'],
                                'cover_url' => $result['cover_url'],
                                'first_publish_year' => $result['first_publish_year'],
                            ],
                        );
                    }
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

        return view('books.index', compact('query', 'results', 'error'));
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
            'myPdfs' => $request->user()->bookPdfs()->where('book_id', $book->id)->latest()->get(),
            'reviews' => $book->reviews()->with('user')->latest()->paginate(10, ['*'], 'reviews_page'),
        ]);
    }
}
