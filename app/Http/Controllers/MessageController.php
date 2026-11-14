<?php

namespace App\Http\Controllers;

use App\Models\Conversation;
use App\Models\Message;
use App\Models\MessageAttachment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
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
            ->with(['user', 'attachments', 'post.photos', 'post.book', 'post.user'])
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
            'seenId' => $this->seenMessageId($conversation, $user),
        ]);
    }

    public function store(Request $request, Conversation $conversation): JsonResponse|RedirectResponse
    {
        $user = $request->user();
        $conversation->load('users');
        abort_unless($conversation->membership($user), 404);

        $validated = $request->validate([
            'body' => ['nullable', 'string', 'max:2000'],
            'photos' => ['nullable', 'array', 'max:10'],
            'photos.*' => ['image', 'mimes:jpg,jpeg,png,webp,gif', 'max:10240'],
            'voice' => ['nullable', 'file', 'mimetypes:audio/webm,audio/ogg,audio/mp4,audio/x-m4a,audio/mpeg,audio/wav,audio/x-wav,audio/aac,video/webm,video/mp4,application/octet-stream', 'max:10240'],
            'voice_duration' => ['nullable', 'numeric', 'min:0', 'max:300'],
            'files' => ['nullable', 'array', 'max:5'],
            'files.*' => ['file', 'mimes:pdf,epub,txt,md,doc,docx,odt,rtf,csv,xls,xlsx,ppt,pptx,zip', 'max:20480'],
        ], [
            'photos.max' => 'You can send up to 10 photos at once.',
            'photos.*.max' => 'Each photo must be smaller than 10 MB.',
            'photos.*.image' => 'That photo couldn’t be read.',
            'voice.max' => 'Voice messages can be up to 10 MB.',
            'voice.mimetypes' => 'That voice message format isn’t supported.',
            'files.max' => 'You can send up to 5 files at once.',
            'files.*.max' => 'Each file must be 20 MB or smaller.',
            'files.*.mimes' => 'You can send documents like PDF, EPUB, Word, text, spreadsheets or ZIP files.',
        ]);

        $body = trim((string) ($validated['body'] ?? '')) ?: null;
        $uploads = $this->uploads($request, $user, $validated);
        if ($body === null && $uploads === []) {
            throw \Illuminate\Validation\ValidationException::withMessages(['body' => 'Write a message or add a photo, voice message or file.']);
        }

        $message = $this->send($conversation, $user, $body, null, $uploads);

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
            ->with(['user', 'attachments', 'post.photos', 'post.book', 'post.user'])
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
            'seen_id' => $this->seenMessageId($conversation, $user),
        ]);
    }

    /** The conversation list, so it can refresh itself while the page is open. */
    public function list(Request $request): JsonResponse
    {
        $user = $request->user();
        $tab = $request->query('tab') === 'requests' ? 'requests' : 'inbox';

        return response()->json([
            'html' => view('messages.partials.list', [
                'conversations' => $this->inbox($user, $tab),
                'tab' => $tab,
                'activeId' => (int) $request->query('active'),
                'me' => $user,
            ])->render(),
            'requests' => $user->messageRequestCount(),
            'unread' => $user->unreadConversationCount() + $user->messageRequestCount(),
        ]);
    }

    /** Just the number for the top-bar badge (checked now and then on every page). */
    public function unread(Request $request): JsonResponse
    {
        $user = $request->user();

        return response()->json(['unread' => $user->unreadConversationCount() + $user->messageRequestCount()]);
    }

    /** A photo, voice message or file — only for the two people in the chat. */
    public function attachment(Request $request, MessageAttachment $attachment)
    {
        $conversation = $attachment->message->conversation;
        abort_unless($conversation->hasMember($request->user()), 404);
        abort_unless(Storage::disk('local')->exists($attachment->path), 404);

        $headers = ['X-Content-Type-Options' => 'nosniff', 'Cache-Control' => 'private, max-age=86400'];
        if ($attachment->type === 'file') {
            return Storage::disk('local')->download($attachment->path, $attachment->original_name ?: 'file', $headers);
        }

        return response()->file(Storage::disk('local')->path($attachment->path), $headers + ['Content-Type' => $attachment->mime ?: 'application/octet-stream']);
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

    private function send(Conversation $conversation, User $sender, ?string $body, ?int $postId = null, array $uploads = []): Message
    {
        $message = $conversation->messages()->create(['user_id' => $sender->id, 'body' => $body, 'post_id' => $postId]);
        foreach ($uploads as $upload) {
            $message->attachments()->create($upload);
        }
        $conversation->forceFill(['last_message_at' => $message->created_at])->save();
        // Writing in a chat accepts it (e.g. replying to a request) and marks it read.
        $conversation->users()->updateExistingPivot($sender->id, [
            'last_read_at' => $message->created_at,
            'accepted_at' => $conversation->membership($sender)?->accepted_at ?? now(),
        ]);

        return $message->load(['user', 'attachments', 'post.photos', 'post.book', 'post.user']);
    }

    /** Store the photos, voice message and files of a new message; returns attachment rows. */
    private function uploads(Request $request, User $user, array $validated): array
    {
        $stored = [];
        $store = function (UploadedFile $file, string $type, array $extra = []) use (&$stored, $user) {
            $path = $file->store('message-attachments/' . $user->id, 'local');
            if ($path === false) {
                throw new \RuntimeException('Failed to store a message attachment.');
            }
            $name = basename(str_replace('\\', '/', $file->getClientOriginalName()));
            $stored[] = [
                'type' => $type,
                'path' => $path,
                'original_name' => mb_substr(preg_replace('/[\x00-\x1F\x7F]/u', '', $name) ?: $type, 0, 255),
                'mime' => $file->getMimeType(),
                'size' => $file->getSize(),
            ] + $extra;
        };

        foreach ($request->file('photos', []) as $photo) {
            [$width, $height] = @getimagesize($photo->getRealPath()) ?: [null, null];
            $store($photo, 'image', ['width' => $width, 'height' => $height]);
        }
        if ($voice = $request->file('voice')) {
            $store($voice, 'audio', ['duration' => $validated['voice_duration'] ?? null]);
        }
        foreach ($request->file('files', []) as $file) {
            $store($file, 'file');
        }

        return $stored;
    }

    /** The newest message of mine the other reader has already seen (for "Seen"). */
    private function seenMessageId(Conversation $conversation, User $user): ?int
    {
        $other = $conversation->users->firstWhere('id', '!=', $user->id);
        $readAt = $other?->pivot->last_read_at;
        if (! $readAt) {
            return null;
        }
        $mine = $conversation->messages()->where('user_id', $user->id)->latest('id')->first();

        return $mine && $mine->created_at->lte(\Illuminate\Support\Carbon::parse($readAt)) ? $mine->id : null;
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
            ->with(['users', 'latestMessage.attachments'])
            ->orderByDesc('last_message_at')
            ->get()
            ->each(function (Conversation $conversation) use ($user) {
                $readAt = $conversation->pivot->last_read_at ? \Illuminate\Support\Carbon::parse($conversation->pivot->last_read_at) : null;
                $latest = $conversation->latestMessage;
                $conversation->setAttribute('unread', $latest && $latest->user_id !== $user->id && (! $readAt || $latest->created_at->gt($readAt)));
            });
    }
}
