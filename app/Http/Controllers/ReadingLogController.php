<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\UserBookShelf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Log reading from the book page: a page for paper and e-books, a time for audiobooks. */
class ReadingLogController extends Controller
{
    public function store(Request $request, Book $book): RedirectResponse
    {
        $validated = $request->validate([
            'format' => ['required', Rule::in(array_keys(UserBookShelf::FORMATS))],
            'current_page' => ['nullable', 'integer', 'min:0', 'max:100000'],
            'total_pages' => ['nullable', 'integer', 'min:1', 'max:100000'],
            'position_h' => ['nullable', 'integer', 'min:0', 'max:200'],
            'position_m' => ['nullable', 'integer', 'min:0', 'max:59'],
            'length_h' => ['nullable', 'integer', 'min:0', 'max:200'],
            'length_m' => ['nullable', 'integer', 'min:0', 'max:59'],
        ]);

        $item = $request->user()->shelfItems()->firstOrNew(['book_id' => $book->id]);
        $item->format = $validated['format'];
        if (! $item->exists || $item->status === 'want_to_read') {
            $item->status = 'reading';
        }

        if ($validated['format'] === 'audio') {
            $length = (int) ($validated['length_h'] ?? 0) * 60 + (int) ($validated['length_m'] ?? 0);
            $position = (int) ($validated['position_h'] ?? 0) * 60 + (int) ($validated['position_m'] ?? 0);
            if ($length > 0) {
                $item->audio_length = $length;
            }
            $item->audio_position = $item->audio_length ? min($position, $item->audio_length) : $position;
            if ($item->audio_length && $item->audio_position >= $item->audio_length) {
                $item->status = 'read';
            }
        } else {
            if (isset($validated['total_pages'])) {
                $item->total_pages = $validated['total_pages'];
            }
            if (isset($validated['current_page'])) {
                $item->current_page = $item->total_pages ? min($validated['current_page'], $item->total_pages) : $validated['current_page'];
                if ($item->total_pages && $item->current_page >= $item->total_pages) {
                    $item->status = 'read';
                }
            }
        }
        if ($item->status === 'read' && ! $item->read_at) {
            $item->read_at = now();
        }
        $item->save();

        return back()->with('status', $item->status === 'read' ? 'Finished! 🎉 Well read.' : 'Logged. Keep going! 📖');
    }
}
