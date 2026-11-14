<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BuddyRead;
use App\Models\User;
use App\Models\UserBookShelf;
use App\Notifications\BookwormNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Buddy reads: read a book with friends, see everyone's bookmark on one
 * progress track, and talk about it without spoilers.
 */
class BuddyReadController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $reads = $user->buddyReads()->with(['book', 'members'])->latest('buddy_reads.updated_at')->get();

        return view('buddy.index', [
            'invites' => $reads->filter(fn ($read) => $read->pivot->joined_at === null)->values(),
            'active' => $reads->filter(fn ($read) => $read->pivot->joined_at !== null)->values(),
            'shelfBooks' => $user->shelfItems()->with('book')->whereIn('status', ['reading', 'want_to_read'])->latest('updated_at')->get()->pluck('book')->filter(),
            'friends' => $user->following()->orderBy('name')->get(),
            'preselectBook' => (int) $request->query('book'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'title' => ['nullable', 'string', 'max:120'],
            'invitees' => ['required', 'array', 'min:1', 'max:12'],
            'invitees.*' => ['integer', Rule::exists('user_follows', 'followed_id')->where('follower_id', $user->id)],
        ], ['invitees.required' => 'Invite at least one reader you follow.']);

        $book = Book::findOrFail($validated['book_id']);
        $read = BuddyRead::create(['book_id' => $book->id, 'creator_id' => $user->id, 'title' => $validated['title'] ?? null]);
        $read->members()->attach($user->id, ['joined_at' => now()]);
        $read->members()->attach(collect($validated['invitees'])->unique()->reject(fn ($id) => $id === $user->id)->all());
        $this->ensureReading($user, $book->id);

        $read->members()->where('users.id', '!=', $user->id)->get()->each(fn (User $member) => BookwormNotification::send(
            $member, 'buddy', $user->name . ' invited you to read “' . $book->title . '” together.', route('buddy.show', $read), $user,
        ));

        return to_route('buddy.show', $read)->with('status', 'Buddy read started — invitations sent.');
    }

    public function show(Request $request, BuddyRead $buddyRead): View
    {
        $user = $request->user();
        abort_unless($buddyRead->hasMember($user, joinedOnly: false), 404);

        $buddyRead->load(['book', 'members', 'comments.user', 'creator']);
        $progress = UserBookShelf::query()
            ->where('book_id', $buddyRead->book_id)
            ->whereIn('user_id', $buddyRead->members->pluck('id'))
            ->get()
            ->keyBy('user_id');

        $members = $buddyRead->members->map(function (User $member) use ($progress) {
            $item = $progress->get($member->id);
            $member->setAttribute('bookmark', $item?->current_page);
            $member->setAttribute('percent', $item?->status === 'read' ? 100 : ($item?->progressPercent() ?? 0));
            $member->setAttribute('joined', $member->pivot->joined_at !== null);

            return $member;
        })->sortByDesc('percent')->values();

        $mine = $progress->get($user->id);

        return view('buddy.show', [
            'read' => $buddyRead,
            'members' => $members,
            'myItem' => $mine,
            'myPage' => $mine?->status === 'read' ? PHP_INT_MAX : (int) ($mine?->current_page ?? 0),
            'joined' => $buddyRead->hasMember($user),
        ]);
    }

    public function join(Request $request, BuddyRead $buddyRead): RedirectResponse
    {
        $user = $request->user();
        abort_unless($buddyRead->hasMember($user, joinedOnly: false), 404);

        $buddyRead->members()->updateExistingPivot($user->id, ['joined_at' => now()]);
        $this->ensureReading($user, $buddyRead->book_id);
        BookwormNotification::send($buddyRead->creator, 'buddy', $user->name . ' joined your buddy read of “' . $buddyRead->book->title . '”.', route('buddy.show', $buddyRead), $user);

        return to_route('buddy.show', $buddyRead)->with('status', 'You joined the buddy read. Happy reading!');
    }

    public function leave(Request $request, BuddyRead $buddyRead): RedirectResponse
    {
        $buddyRead->members()->detach($request->user()->id);

        if ($buddyRead->members()->count() === 0) {
            $buddyRead->delete();
        }

        return to_route('buddy.index')->with('status', 'You left the buddy read.');
    }

    public function comment(Request $request, BuddyRead $buddyRead): RedirectResponse
    {
        $user = $request->user();
        abort_unless($buddyRead->hasMember($user), 404);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
            'page' => ['nullable', 'integer', 'min:0', 'max:100000'],
        ]);

        $comment = $buddyRead->comments()->create([
            'user_id' => $user->id,
            'body' => trim($validated['body']),
            'page' => $validated['page'] ?? null,
        ]);
        $buddyRead->touch();

        $buddyRead->members()->whereNotNull('buddy_read_user.joined_at')->where('users.id', '!=', $user->id)->get()->each(fn (User $member) => BookwormNotification::send(
            $member, 'buddy', $user->name . ' wrote in “' . $buddyRead->book->title . '”' . ($comment->page ? ' (page ' . $comment->page . ')' : '') . '.', route('buddy.show', $buddyRead) . '#comment-' . $comment->id, $user,
        ));

        return redirect()->to(route('buddy.show', $buddyRead) . '#comment-' . $comment->id);
    }

    private function ensureReading(User $user, int $bookId): void
    {
        $item = $user->shelfItems()->firstOrNew(['book_id' => $bookId]);
        if (! $item->exists || $item->status === 'want_to_read') {
            $item->status = 'reading';
            $item->save();
        }
    }
}
