<?php

namespace App\Http\Controllers;

use App\Models\Book;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Readers mark which series a book belongs to (shared by everyone). */
class SeriesController extends Controller
{
    public function update(Request $request, Book $book): RedirectResponse
    {
        $validated = $request->validate([
            'series_name' => ['nullable', 'string', 'max:120'],
            'series_number' => ['nullable', 'numeric', 'min:0', 'max:999'],
        ]);
        $name = trim((string) ($validated['series_name'] ?? ''));

        // Match an existing series name, ignoring case, so it stays one series.
        if ($name !== '') {
            $name = Book::whereRaw('LOWER(series_name) = ?', [mb_strtolower($name)])->value('series_name') ?? $name;
        }
        $book->forceFill([
            'series_name' => $name ?: null,
            'series_number' => $name !== '' ? ($validated['series_number'] ?? null) : null,
        ])->save();

        return back()->with('status', $name ? 'Series saved.' : 'Series removed.');
    }
}
