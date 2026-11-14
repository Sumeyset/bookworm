<?php

namespace App\Support;

use App\Models\User;
use App\Models\UserBookShelf;
use Illuminate\Support\Collection;

/**
 * Works out which books sit on which branch of a reader's tree.
 *
 * The tree always has at least MIN_BRANCHES branches and grows one branch
 * taller for every three books. The owner also gets one empty "sprouting"
 * branch at the top to grow into.
 */
class BookTree
{
    public const MIN_BRANCHES = 3;

    /**
     * @param  Collection<int, UserBookShelf>  $items
     * @return Collection<int, array{slot: int, title: string, items: Collection<int, UserBookShelf>, sprouting: bool}>
     */
    public static function build(User $owner, Collection $items, bool $isOwner): Collection
    {
        $capacity = UserBookShelf::MAX_BOOKS_PER_SHELF;
        $bySlot = [];
        $overflow = [];

        // Keep each book where the reader put it; anything that does not fit
        // (legacy data, or a full branch) goes onto the first branch with room.
        foreach ($items->sortBy([['shelf_slot', 'asc'], ['shelf_order', 'asc'], ['id', 'asc']]) as $item) {
            $slot = (int) $item->shelf_slot;
            if ($slot < UserBookShelf::MAX_SHELVES && count($bySlot[$slot] ?? []) < $capacity) {
                $bySlot[$slot][] = $item;
            } else {
                $overflow[] = $item;
            }
        }

        foreach ($overflow as $item) {
            $slot = 0;
            while (count($bySlot[$slot] ?? []) >= $capacity) {
                $slot++;
            }
            $item->shelf_slot = $slot;
            $bySlot[$slot][] = $item;
        }

        $highestUsed = empty($bySlot) ? -1 : max(array_keys($bySlot));
        $branchCount = max(self::MIN_BRANCHES, $highestUsed + 1);

        // Give the owner a fresh branch to grow into once the top one is in use.
        if ($isOwner && $highestUsed === $branchCount - 1 && $branchCount < UserBookShelf::MAX_SHELVES) {
            $branchCount++;
        }

        // The tree picture shows 7 planks and grows two planks at a time.
        $branchCount = TreeBackdrop::shelvesFor(min($branchCount, UserBookShelf::MAX_SHELVES - 1));

        return collect(range(0, $branchCount - 1))->map(fn (int $slot) => [
            'slot' => $slot,
            'title' => $owner->getTreeShelfName($slot),
            'items' => collect($bySlot[$slot] ?? [])->values(),
            'sprouting' => $isOwner && $slot === $branchCount - 1 && empty($bySlot[$slot]),
        ]);
    }

    /** A friendly name for how grown the tree is. */
    public static function stage(int $bookCount): array
    {
        return match (true) {
            $bookCount >= 30 => ['Ancient arbor', 'A towering tree full of stories.'],
            $bookCount >= 18 => ['Grand oak', 'Your canopy is filling out beautifully.'],
            $bookCount >= 9 => ['Young oak', 'New branches are reaching for the sky.'],
            $bookCount >= 1 => ['Sapling', 'Every book you shelve helps it grow.'],
            default => ['Seedling', 'Shelve your first book to start growing.'],
        };
    }
}
