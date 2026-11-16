<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\ReadingRoom;
use App\Models\User;
use App\Notifications\BookwormNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Reading rooms: a timed session friends join to read at the same time,
 * with a shared timer, who's here (and what they're reading) and a quiet chat.
 */
class ReadingRoomController extends Controller
{
    public const LENGTHS = [25, 45, 60, 90];

    public function index(Request $request): View
    {
        $me = $request->user();
        $friendIds = $me->following()->pluck('users.id')->push($me->id);

        return view('rooms.index', [
            'live' => ReadingRoom::live()
                ->where(fn ($q) => $q->whereIn('host_id', $friendIds)->orWhereHas('members', fn ($m) => $m->where('users.id', $me->id)))
                ->whereNotIn('host_id', $me->blockedWithIds())
                ->with('host')->withCount('members')->orderBy('ends_at')->get(),
            'books' => $me->shelfItems()->with('book')->where('status', 'reading')->get()->pluck('book')->filter(),
            'lengths' => self::LENGTHS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $me = $request->user();
        $validated = $request->validate([
            'title' => ['nullable', 'string', 'max:80'],
            'minutes' => ['required', Rule::in(self::LENGTHS)],
            'book_id' => ['nullable', 'integer', 'exists:books,id'],
        ]);

        $room = ReadingRoom::create([
            'host_id' => $me->id,
            'title' => trim((string) ($validated['title'] ?? '')) ?: \Illuminate\Support\Str::before($me->name, ' ') . '’s reading room',
            'starts_at' => now(),
            'ends_at' => now()->addMinutes((int) $validated['minutes']),
        ]);
        $room->members()->attach($me->id, ['book_id' => $validated['book_id'] ?? null, 'last_seen_at' => now()]);

        $me->followers()->whereNotIn('users.id', $me->blockedWithIds())->get()->each(fn (User $friend) => BookwormNotification::send(
            $friend, 'room', $me->name . ' opened a reading room for ' . $validated['minutes'] . ' minutes — come read together!', route('rooms.show', $room), $me,
        ));

        return to_route('rooms.show', $room);
    }

    public function show(Request $request, ReadingRoom $room): View
    {
        $me = $request->user();
        abort_if($me->isBlockedWith($room->host), 404);

        return view('rooms.show', [
            'room' => $room->load('host'),
            'joined' => $room->members()->where('users.id', $me->id)->exists(),
            'books' => $me->shelfItems()->with('book')->where('status', 'reading')->get()->pluck('book')->filter(),
        ]);
    }

    public function join(Request $request, ReadingRoom $room): RedirectResponse
    {
        $me = $request->user();
        abort_if($me->isBlockedWith($room->host) || ! $room->isLive(), 404);
        $validated = $request->validate(['book_id' => ['nullable', 'integer', 'exists:books,id']]);
        $room->members()->syncWithoutDetaching([$me->id => ['book_id' => $validated['book_id'] ?? null, 'last_seen_at' => now()]]);

        return to_route('rooms.show', $room);
    }

    public function leave(Request $request, ReadingRoom $room): RedirectResponse
    {
        $room->members()->detach($request->user()->id);
        if ($room->host_id === $request->user()->id && $room->isLive()) {
            $room->update(['ends_at' => now()]); // the host leaving ends the session
        }

        return to_route('rooms.index');
    }

    public function message(Request $request, ReadingRoom $room): JsonResponse
    {
        $me = $request->user();
        abort_unless($room->isLive() && $room->members()->where('users.id', $me->id)->exists(), 403);
        $validated = $request->validate(['body' => ['required', 'string', 'max:280']]);
        $room->messages()->create(['user_id' => $me->id, 'body' => trim($validated['body'])]);

        return $this->state($request, $room);
    }

    /** Everything the page needs, polled every few seconds. */
    public function state(Request $request, ReadingRoom $room): JsonResponse
    {
        $me = $request->user();
        abort_if($me->isBlockedWith($room->host), 404);
        $room->members()->updateExistingPivot($me->id, ['last_seen_at' => now()]);

        $after = (int) $request->query('after', 0);
        $members = $room->members()->get();
        $books = Book::whereIn('id', $members->pluck('pivot.book_id')->filter())->get()->keyBy('id');
        $hidden = $me->blockedWithIds();

        return response()->json([
            'live' => $room->isLive(),
            'ends_at' => $room->ends_at->toIso8601String(),
            'seconds_left' => max(0, now()->diffInSeconds($room->ends_at, false)),
            'members' => $members->reject(fn ($m) => $hidden->contains($m->id))->map(fn (User $m) => [
                'name' => $m->id === $me->id ? 'You' : $m->name,
                'username' => $m->username,
                'initial' => mb_strtoupper(mb_substr($m->name, 0, 1)),
                'photo' => $m->profile_photo_url,
                'here' => $m->pivot->last_seen_at && now()->diffInSeconds($m->pivot->last_seen_at, true) < 40,
                'book' => ($book = $books->get($m->pivot->book_id)) ? ['title' => $book->title, 'cover' => $book->cover_url ? $book->cover_src : null] : null,
            ])->values(),
            'messages' => $room->messages()->where('id', '>', $after)->whereNotIn('user_id', $hidden)->with('user')->orderBy('id')->limit(50)->get()
                ->map(fn ($msg) => ['id' => $msg->id, 'name' => $msg->user_id === $me->id ? 'You' : $msg->user->name, 'mine' => $msg->user_id === $me->id, 'body' => $msg->body, 'time' => $msg->created_at->format('H:i')]),
        ]);
    }
}
