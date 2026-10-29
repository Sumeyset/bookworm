<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\UserBookShelf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class LibraryController extends Controller
{
    public function index(Request $request): RedirectResponse
    {
        return to_route('users.show', [
            'user' => $request->user()->username,
            ...$request->only(['status', 'q', 'sort', 'page']),
        ]);
    }

    public function update(Request $request, Book $book): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', array_keys(UserBookShelf::STATUSES))],
            'current_page' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'total_pages' => ['nullable', 'integer', 'min:1', 'max:1000000', 'gte:current_page'],
        ]);

        if (isset($validated['total_pages']) && isset($validated['current_page']) && $validated['current_page'] > $validated['total_pages']) {
            return back()->withErrors(['current_page' => 'Current page cannot be greater than the total page count.'])->withInput();
        }

        $shelfItem = $request->user()->shelfItems()->firstOrNew(['book_id' => $book->id]);
        $wasRead = $shelfItem->exists && $shelfItem->status === 'read' && $shelfItem->read_at !== null;
        $shelfItem->fill([
            'status' => $validated['status'],
            'current_page' => array_key_exists('current_page', $validated) ? $validated['current_page'] : $shelfItem->current_page,
            'total_pages' => array_key_exists('total_pages', $validated) ? $validated['total_pages'] : $shelfItem->total_pages,
            'read_at' => $validated['status'] === 'read' ? ($wasRead ? $shelfItem->read_at : now()) : null,
        ])->save();

        if ($request->expectsJson()) {
            return response()->json(['saved' => true], 200);
        }

        return back()->with('status', 'Your reading shelf has been updated.');
    }
}
