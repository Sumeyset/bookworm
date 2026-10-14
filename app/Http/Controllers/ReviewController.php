<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function store(Request $request, Book $book): RedirectResponse
    {
        $validated = $request->validate([
            'rating' => ['required', 'numeric', 'between:0.5,5', 'multiple_of:0.5'],
            'body' => ['nullable', 'string', 'max:3000'],
        ]);

        $book->reviews()->updateOrCreate(
            ['user_id' => $request->user()->id],
            [
                'rating' => $validated['rating'],
                'body' => $validated['body'] ?? null,
            ],
        );

        return to_route('books.show', $book)->with('status', 'Your review has been saved.');
    }

    public function destroy(Request $request, Review $review): RedirectResponse
    {
        abort_unless($review->user_id === $request->user()->id, 403);

        $book = $review->book;
        $review->delete();

        return to_route('books.show', $book)->with('status', 'Your review has been deleted.');
    }
}
