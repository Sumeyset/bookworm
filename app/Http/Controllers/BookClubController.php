<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookClub;
use App\Models\BookClubPost;
use App\Models\BookClubSection;
use App\Models\User;
use App\Models\UserBookShelf;
use App\Notifications\BookwormNotification;
use App\Support\Challenges;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Book clubs: a group reads one book at a time on a schedule split into
 * sections. Each section has its own discussion, hidden from members whose
 * bookmark hasn't reached it yet (no spoilers).
 */
class BookClubController extends Controller
{
    public const EMOJIS = ['📚', '📖', '🌙', '☕', '🕯️', '🌿', '🐉', '🔎', '💌', '🚀', '🏰', '🎭'];

    public function index(Request $request): View
    {
        $user = $request->user();
        $search = trim((string) $request->query('q'));

        $mine = $user->bookClubs()->with(['currentRound.book', 'owner'])->withCount('joinedMembers')->latest('book_clubs.updated_at')->get();

        $discover = BookClub::query()
            ->where('is_private', false)
            ->whereNotIn('id', $mine->pluck('id'))
            ->when($search !== '', fn ($q) => $q->where(fn ($w) => $w->where('name', 'like', '%' . $search . '%')->orWhere('description', 'like', '%' . $search . '%')))
            ->with(['currentRound.book', 'owner'])
            ->withCount('joinedMembers')
            ->orderByDesc('joined_members_count')
            ->latest()
            ->take(24)
            ->get();

        return view('clubs.index', [
            'invites' => $mine->filter(fn ($club) => $club->pivot->joined_at === null)->values(),
            'clubs' => $mine->filter(fn ($club) => $club->pivot->joined_at !== null)->values(),
            'discover' => $discover,
            'search' => $search,
            'emojis' => self::EMOJIS,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'emoji' => ['nullable', Rule::in(self::EMOJIS)],
            'description' => ['nullable', 'string', 'max:500'],
            'is_private' => ['nullable', 'boolean'],
        ]);

        $club = BookClub::create([
            'owner_id' => $request->user()->id,
            'name' => $validated['name'],
            'emoji' => $validated['emoji'] ?? '📚',
            'description' => $validated['description'] ?? null,
            'is_private' => $request->boolean('is_private'),
        ]);
        $club->members()->attach($request->user()->id, ['role' => 'owner', 'joined_at' => now()]);
        Challenges::check($request->user());

        return to_route('clubs.show', $club)->with('status', 'Your club is ready! Pick the first book.');
    }

    public function show(Request $request, BookClub $club): View
    {
        $user = $request->user();
        abort_unless(! $club->is_private || $club->hasMember($user, joinedOnly: false), 404);

        $club->load(['owner', 'members', 'currentRound.book', 'currentRound.sections.posts.user', 'rounds.book']);
        $round = $club->currentRound;
        $joined = $club->hasMember($user);

        // Everyone's bookmark in the current book.
        $bookmarks = $round
            ? UserBookShelf::where('book_id', $round->book_id)->whereIn('user_id', $club->members->pluck('id'))->get()->keyBy('user_id')
            : collect();
        $pageOf = fn (?UserBookShelf $item) => $item?->status === 'read' ? PHP_INT_MAX : (int) ($item?->current_page ?? 0);
        $members = $club->members->map(function (User $member) use ($bookmarks, $pageOf) {
            $item = $bookmarks->get($member->id);
            $member->setAttribute('page', $pageOf($item));
            $member->setAttribute('percent', $item?->status === 'read' ? 100 : ($item?->progressPercent() ?? 0));
            $member->setAttribute('joined', $member->pivot->joined_at !== null);

            return $member;
        })->sortByDesc(fn ($m) => [$m->joined, $m->percent])->values();

        $myItem = $bookmarks->get($user->id);

        return view('clubs.show', [
            'club' => $club,
            'round' => $round,
            'joined' => $joined,
            'isOwner' => $club->isOwner($user),
            'members' => $members,
            'myItem' => $myItem,
            'myPage' => $pageOf($myItem),
            'past' => $club->rounds->whereNotNull('finished_at')->values(),
            'invitable' => $joined ? $user->following()->whereNotIn('users.id', $club->members->pluck('id'))->orderBy('name')->get() : collect(),
            'shelfBooks' => $club->isOwner($user)
                ? $user->shelfItems()->with('book')->latest('updated_at')->take(60)->get()->pluck('book')->filter()->unique('id')->values()
                : collect(),
            'emojis' => self::EMOJIS,
        ]);
    }

    public function update(Request $request, BookClub $club): RedirectResponse
    {
        abort_unless($club->isOwner($request->user()), 403);
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:80'],
            'emoji' => ['nullable', Rule::in(self::EMOJIS)],
            'description' => ['nullable', 'string', 'max:500'],
            'is_private' => ['nullable', 'boolean'],
        ]);
        $club->update(['name' => $validated['name'], 'emoji' => $validated['emoji'] ?? $club->emoji, 'description' => $validated['description'] ?? null, 'is_private' => $request->boolean('is_private')]);

        return back()->with('status', 'Club updated.');
    }

    public function join(Request $request, BookClub $club): RedirectResponse
    {
        $user = $request->user();
        $invited = $club->hasMember($user, joinedOnly: false);
        abort_unless(! $club->is_private || $invited, 404);

        if ($invited) {
            $club->members()->updateExistingPivot($user->id, ['joined_at' => now()]);
        } else {
            $club->members()->attach($user->id, ['role' => 'member', 'joined_at' => now()]);
        }
        if ($club->currentRound) {
            $this->ensureOnShelf($user, $club->currentRound->book_id);
        }
        BookwormNotification::send($club->owner, 'club', $user->name . ' joined ' . $club->name . '.', route('clubs.show', $club), $user);
        Challenges::check($user);

        return to_route('clubs.show', $club)->with('status', 'Welcome to ' . $club->name . '!');
    }

    public function leave(Request $request, BookClub $club): RedirectResponse
    {
        $user = $request->user();
        if ($club->isOwner($user)) {
            // The owner hands the club to the longest-standing member, or closes it.
            $next = $club->joinedMembers()->where('users.id', '!=', $user->id)->orderBy('book_club_members.joined_at')->first();
            if (! $next) {
                $club->delete();

                return to_route('clubs.index')->with('status', 'Club closed.');
            }
            $club->update(['owner_id' => $next->id]);
            $club->members()->updateExistingPivot($next->id, ['role' => 'owner']);
            BookwormNotification::send($next, 'club', 'You now run ' . $club->name . '.', route('clubs.show', $club), $user);
        }
        $club->members()->detach($user->id);

        return to_route('clubs.index')->with('status', $request->boolean('decline') ? 'Invitation declined.' : 'You left ' . $club->name . '.');
    }

    public function invite(Request $request, BookClub $club): RedirectResponse
    {
        $user = $request->user();
        abort_unless($club->hasMember($user), 404);
        $validated = $request->validate([
            'invitees' => ['required', 'array', 'min:1', 'max:20'],
            'invitees.*' => ['integer', Rule::exists('user_follows', 'followed_id')->where('follower_id', $user->id)],
        ]);

        $existing = $club->members()->pluck('users.id');
        User::whereIn('id', collect($validated['invitees'])->diff($existing))->get()->each(function (User $friend) use ($club, $user) {
            $club->members()->attach($friend->id, ['role' => 'member']);
            BookwormNotification::send($friend, 'club', $user->name . ' invited you to the book club ' . $club->name . '.', route('clubs.show', $club), $user);
        });

        return back()->with('status', 'Invitations sent.');
    }

    /** Start a new book: split it into sections, a section every few days. */
    public function startRound(Request $request, BookClub $club): RedirectResponse
    {
        abort_unless($club->isOwner($request->user()), 403);
        $validated = $request->validate([
            'book_id' => ['required', 'integer', 'exists:books,id'],
            'pages' => ['required', 'integer', 'min:10', 'max:5000'],
            'sections' => ['required', 'integer', 'min:1', 'max:20'],
            'every_days' => ['required', 'integer', 'min:1', 'max:30'],
            'starts_on' => ['required', 'date'],
        ]);

        $book = Book::findOrFail($validated['book_id']);
        DB::transaction(function () use ($club, $book, $validated) {
            $club->rounds()->whereNull('finished_at')->update(['finished_at' => now()]);
            $round = $club->rounds()->create(['book_id' => $book->id, 'starts_on' => $validated['starts_on']]);
            foreach (self::schedule((int) $validated['pages'], (int) $validated['sections'], (int) $validated['every_days'], Carbon::parse($validated['starts_on'])) as $section) {
                $round->sections()->create($section);
            }
            if (! $book->page_count) {
                $book->update(['page_count' => (int) $validated['pages']]);
            }
        });

        $club->joinedMembers()->get()->each(function (User $member) use ($club, $book, $request) {
            $this->ensureOnShelf($member, $book->id);
            BookwormNotification::send($member, 'club', $club->name . ' is reading “' . $book->title . '” next — the schedule is up!', route('clubs.show', $club), $request->user());
        });

        return back()->with('status', 'New book and schedule set.');
    }

    public function finishRound(Request $request, BookClub $club): RedirectResponse
    {
        abort_unless($club->isOwner($request->user()), 403);
        $club->rounds()->whereNull('finished_at')->update(['finished_at' => now()]);

        return back()->with('status', 'Book finished! Pick the next one when you’re ready.');
    }

    public function post(Request $request, BookClub $club, BookClubSection $section): RedirectResponse
    {
        $user = $request->user();
        abort_unless($club->hasMember($user) && $section->round->book_club_id === $club->id, 404);
        $validated = $request->validate(['body' => ['required', 'string', 'max:2000']]);

        $post = $section->posts()->create(['user_id' => $user->id, 'body' => $validated['body']]);
        $club->touch();

        return redirect()->to(route('clubs.show', $club) . '#club-post-' . $post->id);
    }

    public function destroyPost(Request $request, BookClub $club, BookClubPost $post): RedirectResponse
    {
        $user = $request->user();
        abort_unless($post->section->round->book_club_id === $club->id && ($post->user_id === $user->id || $club->isOwner($user)), 403);
        $post->delete();

        return back()->with('status', 'Message removed.');
    }

    public function removeMember(Request $request, BookClub $club, User $member): RedirectResponse
    {
        abort_unless($club->isOwner($request->user()) && ! $club->isOwner($member), 403);
        $club->members()->detach($member->id);

        return back()->with('status', $member->name . ' was removed from the club.');
    }

    /**
     * Split a book into even sections, a section every $everyDays days.
     *
     * @return list<array{number: int, title: string, from_page: int, to_page: int, due_on: string}>
     */
    public static function schedule(int $pages, int $sections, int $everyDays, Carbon $start): array
    {
        $sections = max(1, min($sections, $pages));
        $out = [];
        $from = 1;
        for ($n = 1; $n <= $sections; $n++) {
            $to = $n === $sections ? $pages : (int) round($pages * $n / $sections);
            $out[] = [
                'number' => $n,
                'title' => $sections === 1 ? 'The whole book' : "Part {$n}",
                'from_page' => $from,
                'to_page' => $to,
                'due_on' => $start->copy()->addDays($everyDays * $n)->toDateString(),
            ];
            $from = $to + 1;
        }

        return $out;
    }

    private function ensureOnShelf(User $user, int $bookId): void
    {
        $item = $user->shelfItems()->firstOrNew(['book_id' => $bookId]);
        if (! $item->exists || $item->status === 'want_to_read') {
            $item->status = 'reading';
            $item->save();
        }
    }
}
