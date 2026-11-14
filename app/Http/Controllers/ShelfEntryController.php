<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\UserBookShelf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * JSON endpoints used by the tree bookshelf: the open-book popup saves through
 * update(), and drag & drop between branches goes through move().
 */
class ShelfEntryController extends Controller
{
    public function update(Request $request, Book $book): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['sometimes', 'required', Rule::in(array_keys(UserBookShelf::STATUSES))],
            'current_page' => ['sometimes', 'nullable', 'integer', 'min:0', 'max:1000000'],
            'total_pages' => ['sometimes', 'nullable', 'integer', 'min:1', 'max:1000000'],
            'note' => ['sometimes', 'nullable', 'string', 'max:3000'],
            'rating' => ['sometimes', 'nullable', 'numeric', 'between:0.5,5', 'multiple_of:0.5'],
        ]);

        $item = $request->user()->shelfItems()->where('book_id', $book->id)->firstOrFail();

        $current = array_key_exists('current_page', $validated) ? $validated['current_page'] : $item->current_page;
        $total = array_key_exists('total_pages', $validated) ? $validated['total_pages'] : $item->total_pages;
        if ($current !== null && $total !== null && $current > $total) {
            throw ValidationException::withMessages([
                'current_page' => 'Your bookmark can’t be past the last page.',
            ]);
        }

        $status = $validated['status'] ?? $item->status;
        $wasRead = $item->status === 'read' && $item->read_at !== null;

        $item->fill([
            'status' => $status,
            'current_page' => $current,
            'total_pages' => $total,
            'note' => array_key_exists('note', $validated) ? $validated['note'] : $item->note,
            'read_at' => $status === 'read' ? ($wasRead ? $item->read_at : now()) : null,
        ]);

        // Finishing a book moves the bookmark to the last page.
        if ($status === 'read' && $total) {
            $item->current_page = $total;
        }

        $item->save();

        if (array_key_exists('rating', $validated)) {
            if ($validated['rating'] === null) {
                $book->reviews()->where('user_id', $request->user()->id)->whereNull('body')->delete();
            } else {
                $book->reviews()->updateOrCreate(
                    ['user_id' => $request->user()->id],
                    ['rating' => $validated['rating']],
                );
            }
        }

        $item->setRelation('book', $book->load([
            'reviews' => fn ($reviews) => $reviews->where('user_id', $request->user()->id),
            'bookPdfs' => fn ($pdfs) => $pdfs->where('user_id', $request->user()->id)->latest(),
        ]));

        return response()->json([
            'saved' => true,
            'book' => $item->treePayload(true),
        ]);
    }

    /**
     * Move a book to another branch. If the branch is full, the book swaps
     * places with the book at the requested position.
     */
    public function move(Request $request, Book $book): JsonResponse
    {
        $validated = $request->validate([
            'slot' => ['required', 'integer', 'min:0', 'max:' . (UserBookShelf::MAX_SHELVES - 1)],
            'position' => ['nullable', 'integer', 'min:0', 'max:' . (UserBookShelf::MAX_BOOKS_PER_SHELF - 1)],
        ]);

        $user = $request->user();
        $item = $user->shelfItems()->where('book_id', $book->id)->firstOrFail();
        $fromSlot = (int) $item->shelf_slot;
        $toSlot = (int) $validated['slot'];

        DB::transaction(function () use ($user, $item, $fromSlot, $toSlot, $validated): void {
            $target = $user->shelfItems()
                ->where('shelf_slot', $toSlot)
                ->whereKeyNot($item->id)
                ->orderBy('shelf_order')
                ->orderBy('id')
                ->get();

            $position = min($validated['position'] ?? $target->count(), $target->count());

            if ($fromSlot !== $toSlot && $target->count() >= UserBookShelf::MAX_BOOKS_PER_SHELF) {
                // Full branch: swap with the book currently at that position.
                $position = min($position, $target->count() - 1);
                $swapped = $target->get($position);
                $swapped->forceFill(['shelf_slot' => $fromSlot, 'shelf_order' => $item->shelf_order])->save();
                $target = $target->reject(fn ($other) => $other->is($swapped))->values();
            }

            $target->splice($position, 0, [$item]);
            $target->values()->each(fn ($entry, $order) => $entry->forceFill([
                'shelf_slot' => $toSlot,
                'shelf_order' => $order,
            ])->save());

            if ($fromSlot !== $toSlot) {
                $this->compact($user->id, $fromSlot);
            }
        });

        return response()->json([
            'moved' => true,
            'shelves' => $this->orders($user->id, array_unique([$fromSlot, $toSlot])),
        ]);
    }

    private function compact(int $userId, int $slot): void
    {
        UserBookShelf::query()
            ->where('user_id', $userId)
            ->where('shelf_slot', $slot)
            ->orderBy('shelf_order')
            ->orderBy('id')
            ->get()
            ->each(fn ($entry, $order) => $entry->forceFill(['shelf_order' => $order])->save());
    }

    /** @return array<int, list<int>> slot => ordered book ids */
    private function orders(int $userId, array $slots): array
    {
        $orders = array_fill_keys($slots, []);

        UserBookShelf::query()
            ->where('user_id', $userId)
            ->whereIn('shelf_slot', $slots)
            ->orderBy('shelf_order')
            ->orderBy('id')
            ->get(['book_id', 'shelf_slot'])
            ->each(function ($entry) use (&$orders): void {
                $orders[$entry->shelf_slot][] = $entry->book_id;
            });

        return $orders;
    }
}
