<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

/**
 * Direct messages, Instagram style: an inbox, message requests from people
 * you don't follow, and a chat that updates on its own.
 */
class MessageController extends Controller
{
    private const PAGE = 40;

    public function index(Request $request): View
    {
        $user = $request->user();
        $tab = $request->query('tab') === 'requests' ? 'requests' : 'inbox';

        return view('messages.index', [
            'tab' => $tab,
            'conversations' => $this->inbox($user, $tab),
            'requestCount' => $user->messageRequestCount(),
            'active' => null,
        ]);
    }

    /** "Message" on a profile: open (or start) the chat with that reader. */
    public function start(Request $request, User $user): RedirectResponse
    {
        abort_if($user->is($request->user()), 404);

        return to_route('messages.show', Conversation::between($request->user(), $user));
    }

    public function show(Request $request, Conversation $conversation): View
    {
        $user = $request->user();
        $conversation->load('users');
        abort_unless($conversation->membership($user), 404);

        $messages = $conversation->messages()
            ->with(['user', 'post.photos', 'post.book', 'post.user'])
            ->latest('id')
            ->take(self::PAGE)
            ->get()
            ->reverse()
            ->values();

        $this->markRead($conversation, $user);
        $isRequest = $conversation->membership($user)->accepted_at === null;

        return view('messages.index', [
            'tab' => $isRequest ? 'requests' : 'inbox',
            'conversations' => $this->inbox($user, $isRequest ? 'requests' : 'inbox'),
            'requestCount' => $user->messageRequestCount(),
            'active' => $conversation,
            'other' => $conversation->other($user),
            'messages' => $messages,
            'isRequest' => $isRequest,
            'hasOlder' => $conversation->messages()->count() > self::PAGE,
        ]);
    }

    public function store(Request $request, Conversation $conversation): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $conversation->load('users');
        abort_unless($conversation->membership($user), 404);

        $validated = $request->validate([
            'body' => ['required', 'string', 'max:2000'],
        ]);

        $message = $this->send($conversation, $user, trim($validated['body']));

        if ($request->expectsJson()) {
            return response()->json(['id' => $message->id, 'html' => $this->render(collect([$message]), $user)], 201);
        }

        return to_route('messages.show', $conversation);
    }

    /** New messages since the last one on screen (the chat asks every few seconds). */
    public function poll(Request $request, Conversation $conversation): JsonResponse
    {
        $user = $request->user();
        $conversation->load('users');
        abort_unless($conversation->membership($user), 404);

        $messages = $conversation->messages()
            ->with(['user', 'post.photos', 'post.book', 'post.user'])
            ->where('id', '>', (int) $request->query('after', 0))
            ->oldest('id')
            ->take(50)
            ->get();

        if ($messages->isNotEmpty()) {
            $this->markRead($conversation, $user);
        }

        return response()->json([
            'last_id' => $messages->last()?->id,
            'html' => $messages->isEmpty() ? '' : $this->render($messages, $user),
            'unread' => $user->unreadConversationCount() + $user->messageRequestCount(),
        ]);
    }

    public function accept(Request $request, Conversation $conversation): RedirectResponse
    {
        $user = $request->user();
        abort_unless($conversation->hasMember($user), 404);
        $conversation->users()->updateExistingPivot($user->id, ['accepted_at' => now()]);

        return to_route('messages.show', $conversation)->with('status', 'Request accepted.');
    }

    /** Decline a message request: the chat goes away for both readers. */
    public function destroy(Request $request, Conversation $conversation): RedirectResponse
    {
        $user = $request->user();
        $conversation->load('users');
        abort_unless($conversation->membership($user), 404);
        abort_unless($conversation->membership($user)->accepted_at === null, 403);

        $conversation->delete();

        return to_route('messages.index', ['tab' => 'requests'])->with('status', 'Request deleted.');
    }

    /** The send (paper plane) button on a post: share it with one or more readers. */
    public function sendPost(Request $request, Post $post): JsonResponse
    {
        $user = $request->user();
        $validated = $request->validate([
            'recipients' => ['required', 'array', 'min:1', 'max:15'],
            'recipients.*' => ['string', Rule::exists('users', 'username')],
            'body' => ['nullable', 'string', 'max:2000'],
        ], ['recipients.required' => 'Choose who to send it to.']);

        $recipients = User::whereIn('username', $validated['recipients'])->whereKeyNot($user->id)->get();
        foreach ($recipients as $recipient) {
            $conversation = Conversation::between($user, $recipient)->load('users');
            $this->send($conversation, $user, null, $post->id);
            if (filled($validated['body'] ?? null)) {
                $this->send($conversation, $user, trim($validated['body']));
            }
        }

        return response()->json(['sent' => $recipients->count()], 201);
    }

    /** People to offer in "Send to": recent chats first, then readers you follow. */
    public function contacts(Request $request): JsonResponse
    {
        $user = $request->user();
        $recent = $user->conversations()
            ->whereNotNull('conversation_user.accepted_at')
            ->with('users')
            ->orderByDesc('last_message_at')
            ->take(8)
            ->get()
            ->map(fn (Conversation $conversation) => $conversation->other($user))
            ->filter();
        $people = $recent->concat($user->following()->orderBy('name')->take(30)->get())->unique('id')->take(30);

        return response()->json($people->values()->map(fn (User $person) => [
            'name' => $person->name,
            'username' => $person->username,
            'photo' => $person->profile_photo_url,
        ]));
    }

    private function send(Conversation $conversation, User $sender, ?string $body, ?int $postId = null): Message
    {
        $message = $conversation->messages()->create(['user_id' => $sender->id, 'body' => $body, 'post_id' => $postId]);
        $conversation->forceFill(['last_message_at' => $message->created_at])->save();
        // Writing in a chat accepts it (e.g. replying to a request) and marks it read.
        $conversation->users()->updateExistingPivot($sender->id, [
            'last_read_at' => $message->created_at,
            'accepted_at' => $conversation->membership($sender)?->accepted_at ?? now(),
        ]);

        return $message->load(['user', 'post.photos', 'post.book', 'post.user']);
    }

    private function markRead(Conversation $conversation, User $user): void
    {
        $conversation->users()->updateExistingPivot($user->id, ['last_read_at' => now()]);
    }

    private function render(Collection $messages, User $viewer): string
    {
        return view('messages.partials.messages', ['messages' => $messages, 'viewer' => $viewer])->render();
    }

    /** Conversations for the list on the left, with unread state worked out. */
    private function inbox(User $user, string $tab): Collection
    {
        return $user->conversations()
            ->when($tab === 'requests', fn ($query) => $query->whereNull('conversation_user.accepted_at'))
            ->when($tab === 'inbox', fn ($query) => $query->whereNotNull('conversation_user.accepted_at'))
            ->whereHas('messages')
            ->with(['users', 'latestMessage'])
            ->orderByDesc('last_message_at')
            ->get()
            ->each(function (Conversation $conversation) use ($user) {
                $readAt = $conversation->pivot->last_read_at ? \Illuminate\Support\Carbon::parse($conversation->pivot->last_read_at) : null;
                $latest = $conversation->latestMessage;
                $conversation->setAttribute('unread', $latest && $latest->user_id !== $user->id && (! $readAt || $latest->created_at->gt($readAt)));
            });
    }
}
