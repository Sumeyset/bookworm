<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookComment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BookCommentController extends Controller
{
    public function store(Request $request, Book $book): RedirectResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:3000'],
        ]);

        $book->comments()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['body' => $validated['body']],
        );

        return to_route('books.show', $book)->with('status', 'Your comment has been saved.');
    }

    public function destroy(Request $request, Book $book, BookComment $comment): RedirectResponse
    {
        abort_unless($comment->book_id === $book->id, 404);
        abort_unless($comment->user_id === $request->user()->id, 403);

        $comment->delete();

        return to_route('books.show', $book)->with('status', 'Your comment has been deleted.');
    }

    public function vote(Request $request, Book $book, BookComment $comment): RedirectResponse
    {
        abort_unless($comment->book_id === $book->id, 404);

        $validated = $request->validate([
            'value' => ['required', 'integer', 'in:-1,1'],
        ]);

        DB::transaction(function () use ($request, $comment, $validated) {
            $vote = $comment->votes()->where('user_id', $request->user()->id)->first();

            if ($vote?->value === (int) $validated['value']) {
                $vote->delete();

                return;
            }

            $comment->votes()->updateOrCreate(
                ['user_id' => $request->user()->id],
                ['value' => $validated['value']],
            );
        });

        return to_route('books.show', $book)->withFragment('comment-' . $comment->id);
    }
}
