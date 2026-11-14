<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BuddyRead;
use App\Models\UserBookShelf;
use App\Support\Barnaby\Barnaby;
use App\Support\Barnaby\BarnabyClient;
use App\Support\Barnaby\BarnabyUnavailable;
use Closure;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/** Barnaby the worm: chat, recommendations, passage help, recaps, discussion questions. */
class BarnabyController extends Controller
{
    public function __construct(private Barnaby $barnaby)
    {
    }

    public function chat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'message' => ['required', 'string', 'max:1500'],
            'history' => ['nullable', 'array', 'max:20'],
            'history.*.role' => ['required', 'in:user,assistant'],
            'history.*.text' => ['required', 'string', 'max:6000'],
            'book_id' => ['nullable', 'integer', 'exists:books,id'],
            'page' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ]);

        return $this->answer($request, fn () => $this->barnaby->chat(
            $request->user(),
            trim($validated['message']),
            $validated['history'] ?? [],
            isset($validated['book_id']) ? Book::find($validated['book_id']) : null,
            $validated['page'] ?? null,
        ));
    }

    public function explain(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'text' => ['required', 'string', 'max:2000'],
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'page' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ]);

        return $this->answer($request, fn () => ['reply' => $this->barnaby->explain(Book::findOrFail($validated['book_id']), $validated['text'], $validated['page'] ?? null)]);
    }

    public function recap(Request $request, Book $book): JsonResponse
    {
        $validated = $request->validate(['page' => ['nullable', 'integer', 'min:1', 'max:100000']]);
        $item = $request->user()->shelfItems()->where('book_id', $book->id)->first();
        $page = (int) ($validated['page'] ?? $item?->current_page ?? 0);

        if ($page < 1) {
            return response()->json(['reply' => 'Move your bookmark first (or read a few pages), and Barnaby will catch you up on everything up to there. 🔖'], 200);
        }

        return $this->answer($request, fn () => ['reply' => $this->barnaby->recap($request->user(), $book, $page, $item?->total_pages ?? $book->page_count), 'page' => $page]);
    }

    public function discussion(Request $request, BuddyRead $buddyRead): JsonResponse
    {
        abort_unless($buddyRead->hasMember($request->user()), 404);

        // Spoiler-safe for everyone: only up to the page the slowest joined member has reached.
        $joined = $buddyRead->members()->whereNotNull('buddy_read_user.joined_at')->pluck('users.id');
        $items = UserBookShelf::query()->where('book_id', $buddyRead->book_id)->whereIn('user_id', $joined)->get();
        $page = $joined->map(fn ($id) => (int) ($items->firstWhere('user_id', $id)?->status === 'read' ? PHP_INT_MAX : ($items->firstWhere('user_id', $id)?->current_page ?? 0)))->min() ?? 0;
        $page = $page === PHP_INT_MAX ? (int) ($items->max('total_pages') ?: $buddyRead->book->page_count ?: 1000) : $page;

        return $this->answer($request, fn () => ['reply' => $this->barnaby->discussion($buddyRead->load('book'), max(0, $page)), 'page' => $page]);
    }

    private function answer(Request $request, Closure $work): JsonResponse
    {
        if (! BarnabyClient::configured()) {
            return response()->json(['reply' => 'Barnaby is still asleep in his book 💤 (No AI key is set up yet: add GEMINI_API_KEY or GROQ_API_KEY to .env.)', 'offline' => true], 503);
        }

        [$allowed, $reason] = $this->barnaby->allow($request->user());
        if (! $allowed) {
            return response()->json(['reply' => $reason, 'limited' => true], 429);
        }

        try {
            $result = $work();
        } catch (BarnabyUnavailable) {
            return response()->json(['reply' => 'Barnaby got lost between the pages and couldn’t answer just now. Please try again in a minute. 🪱', 'offline' => true], 503);
        }

        return response()->json($result + ['remaining' => $this->barnaby->remainingToday($request->user())]);
    }
}
