<?php

namespace App\Http\Controllers;

use App\Models\ShelfNote;
use App\Models\UserBookShelf;
use App\Notifications\BookwormNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Sticky notes left on a book on a friend's shelf. */
class ShelfNoteController extends Controller
{
    public function store(Request $request, UserBookShelf $item): JsonResponse
    {
        $me = $request->user();
        $owner = $item->user;
        abort_if($owner->is($me) || ! $owner->visibleTo($me), 404);
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:200'],
            'color' => ['nullable', Rule::in(ShelfNote::COLORS)],
        ]);
        abort_if(ShelfNote::where('author_id', $me->id)->where('user_book_shelf_id', $item->id)->count() >= 5, 422, 'You can leave up to 5 notes on one book.');

        $note = $item->stickyNotes()->create(['author_id' => $me->id, 'body' => trim($validated['body']), 'color' => $validated['color'] ?? 'yellow']);
        BookwormNotification::send($owner, 'note', $me->name . ' left a sticky note on “' . $item->book->title . '”: “' . str($note->body)->limit(60) . '”', route('users.show', $owner->username) . '#bookshelf', $me);

        return response()->json(['notes' => $item->stickyNotesFor($me)]);
    }

    public function destroy(Request $request, ShelfNote $note): JsonResponse
    {
        $me = $request->user();
        $item = $note->shelfItem;
        abort_unless($note->author_id === $me->id || $item->user_id === $me->id, 403);
        $note->delete();

        return response()->json(['notes' => $item->stickyNotesFor($me)]);
    }
}
