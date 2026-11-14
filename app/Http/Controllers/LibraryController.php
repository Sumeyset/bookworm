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
            'shelf_slot' => ['nullable', 'integer', 'min:0', 'max:' . (UserBookShelf::MAX_SHELVES - 1)],
            'shelf_order' => ['nullable', 'integer', 'min:0', 'max:2'],
            'current_page' => ['nullable', 'integer', 'min:0', 'max:1000000'],
            'total_pages' => ['nullable', 'integer', 'min:1', 'max:1000000', 'gte:current_page'],
        ]);

        if (isset($validated['total_pages']) && isset($validated['current_page']) && $validated['current_page'] > $validated['total_pages']) {
            return back()->withErrors(['current_page' => 'Current page cannot be greater than the total page count.'])->withInput();
        }

        $user = $request->user();
        $shelfItem = $user->shelfItems()->firstOrNew(['book_id' => $book->id]);

        // If assigning to a specific tree shelf slot, ensure max 3 books per shelf
        if (array_key_exists('shelf_slot', $validated) && $validated['shelf_slot'] !== null) {
            $targetSlot = (int) $validated['shelf_slot'];
            $existingCount = $user->shelfItems()
                ->where('shelf_slot', $targetSlot)
                ->where('book_id', '!=', $book->id)
                ->count();

            if ($existingCount >= UserBookShelf::MAX_BOOKS_PER_SHELF) {
                if ($request->expectsJson()) {
                    return response()->json(['error' => 'This shelf is full (maximum 3 books allowed).'], 422);
                }
                return back()->withErrors(['shelf_slot' => 'This branch shelf already holds the maximum of 3 books. Remove or move one to add another.']);
            }

            $shelfItem->shelf_slot = $targetSlot;
            $shelfItem->shelf_order = $validated['shelf_order'] ?? min(2, $existingCount);
        }

        $wasRead = $shelfItem->exists && $shelfItem->status === 'read' && $shelfItem->read_at !== null;
        $shelfItem->fill([
            'status' => $validated['status'],
            'current_page' => array_key_exists('current_page', $validated) ? $validated['current_page'] : $shelfItem->current_page,
            'total_pages' => array_key_exists('total_pages', $validated) ? $validated['total_pages'] : $shelfItem->total_pages,
            'read_at' => $validated['status'] === 'read' ? ($wasRead ? $shelfItem->read_at : now()) : null,
        ])->save();

        if ($request->expectsJson()) {
            return response()->json(['saved' => true, 'shelf_slot' => $shelfItem->shelf_slot], 200);
        }

        return back()->with('status', 'Your reading shelf has been updated.');
    }

    public function updateShelfTitle(Request $request, int $slot): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        if ($slot < 0 || $slot >= UserBookShelf::MAX_SHELVES) {
            abort(404);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'min:1', 'max:50'],
        ]);

        $user = $request->user();
        $shelfNames = $user->tree_shelf_names ?? [];
        $shelfNames[$slot] = trim($validated['title']);

        $user->forceFill(['tree_shelf_names' => $shelfNames])->save();

        if ($request->expectsJson()) {
            return response()->json(['saved' => true, 'slot' => $slot, 'title' => $shelfNames[$slot]], 200);
        }

        return back()->with('status', 'Shelf title updated to "' . e($shelfNames[$slot]) . '".');
    }

    public function removeBookFromShelf(Request $request, int $slot, Book $book): RedirectResponse|\Illuminate\Http\JsonResponse
    {
        $user = $request->user();
        $shelfItem = $user->shelfItems()->where('book_id', $book->id)->first();

        if ($shelfItem) {
            $shelfItem->delete();

            // Close the gap on that branch.
            $user->shelfItems()
                ->where('shelf_slot', $shelfItem->shelf_slot)
                ->orderBy('shelf_order')
                ->orderBy('id')
                ->get()
                ->each(fn ($entry, $order) => $entry->forceFill(['shelf_order' => $order])->save());
        }

        if ($request->expectsJson()) {
            return response()->json(['removed' => true], 200);
        }

        return back()->with('status', 'Book removed from shelf.');
    }
}
