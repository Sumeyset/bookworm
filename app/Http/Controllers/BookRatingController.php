<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BookRatingController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ol_id' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'author_name' => ['nullable', 'string', 'max:255'],
            'cover_url' => ['nullable', 'url', 'max:255'],
            'first_publish_year' => ['nullable', 'integer', 'min:0', 'max:65535'],
            'rating' => ['required', 'numeric', 'between:0.5,5', 'multiple_of:0.5'],
        ]);

        $book = Book::updateOrCreate(
            ['ol_id' => $validated['ol_id']],
            [
                'title' => $validated['title'],
                'author_name' => $validated['author_name'] ?? null,
                'cover_url' => $validated['cover_url'] ?? null,
                'first_publish_year' => $validated['first_publish_year'] ?? null,
            ],
        );

        $book->reviews()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['rating' => $validated['rating']],
        );

        return back()->with('status', 'Your rating has been saved.');
    }
}
