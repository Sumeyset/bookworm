<x-app-layout>
    <x-slot name="title">{{ isset($other) && $other ? $other->name . ' · Messages' : 'Messages' }} · Bookworm</x-slot>

    @php $me = auth()->user(); @endphp

    <div class="page-shell py-4 sm:py-6">
        <div class="dm {{ $active ? 'dm--chat-open' : '' }}">
            {{-- Conversation list --}}
            <aside class="dm-list" aria-label="Conversations">
                <header class="dm-list__head">
                    <h1 class="truncate font-semibold">{{ $me->username }}</h1>
                    <button type="button" class="ig-icon !h-10 !w-10" data-dm-new aria-label="New message" title="New message">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 20h4L19 9l-4-4L4 16v4ZM14 6l4 4"/></svg>
                    </button>
                </header>

                <nav class="dm-tabs" aria-label="Messages or requests">
                    <a href="{{ route('messages.index') }}" @class(['is-active' => $tab === 'inbox']) @if ($tab === 'inbox') aria-current="page" @endif>Messages</a>
                    <a href="{{ route('messages.index', ['tab' => 'requests']) }}" @class(['is-active' => $tab === 'requests']) @if ($tab === 'requests') aria-current="page" @endif>
                        Requests <span class="dm-tabs__count" data-dm-requests @if (! $requestCount) hidden @endif>{{ $requestCount }}</span>
                    </a>
                </nav>

                @if ($tab === 'requests')
                    <p class="px-4 pb-2 text-xs text-tan">Messages from readers you don’t follow. They won’t know you’ve seen them until you reply or accept.</p>
                @endif

                <ul class="dm-list__items" data-dm-list data-list-url="{{ route('messages.list', array_filter(['tab' => $tab === 'requests' ? 'requests' : null, 'active' => $active?->id])) }}" aria-live="polite">
                    @include('messages.partials.list', ['activeId' => $active?->id, 'me' => $me])
                </ul>
            </aside>

            {{-- Chat --}}
            <section class="dm-chat" aria-label="Chat">
                @if ($active && $other)
                    <header class="dm-chat__head">
                        <a href="{{ route('messages.index', $isRequest ? ['tab' => 'requests'] : []) }}" class="ig-icon !h-10 !w-10 md:!hidden" aria-label="Back to messages">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                        </a>
                        <a href="{{ route('users.show', $other->username) }}" class="flex min-w-0 flex-1 items-center gap-3">
                            <x-avatar :src="$other->profile_photo_url" :name="$other->name" size="sm" class="!ring-0 !ring-offset-0" />
                            <span class="min-w-0 leading-tight">
                                <span class="block truncate font-semibold text-ink dark:text-[#F4E9E2]">{{ $other->name }}</span>
                                <span class="block truncate text-xs text-tan">{{ '@' . $other->username }}</span>
                            </span>
                        </a>
                    </header>

                    <div class="dm-chat__scroll" data-dm-scroll>
                        <div class="dm-chat__intro">
                            <x-avatar :src="$other->profile_photo_url" :name="$other->name" size="lg" class="!ring-0 !ring-offset-0" />
                            <p class="mt-2 font-semibold text-ink dark:text-[#F4E9E2]">{{ $other->name }}</p>
                            <p class="text-sm text-tan">{{ '@' . $other->username }} · Bookworm</p>
                            <a href="{{ route('users.show', $other->username) }}" class="btn-ghost mt-3 min-h-0 py-1.5 text-xs">View profile</a>
                            @if ($hasOlder)<p class="mt-4 text-xs text-tan">Showing the latest messages</p>@endif
                        </div>
                        <div class="dm-chat__messages" data-dm-messages data-seen-id="{{ $seenId }}">
                            @include('messages.partials.messages', ['messages' => $messages, 'viewer' => $me])
                        </div>
                        <p class="dm-seen" data-dm-seen hidden>Seen</p>
                    </div>

                    @if ($isRequest)
                        <div class="dm-request">
                            <p class="text-sm text-ink dark:text-[#F4E9E2]"><b>{{ $other->name }}</b> wants to send you a message.</p>
                            <p class="text-xs text-tan">Accept to move this chat to your inbox. They won’t know you’ve seen it until you accept or reply.</p>
                            <div class="mt-3 flex gap-2">
                                <form method="POST" action="{{ route('messages.destroy', $active) }}" class="flex-1" onsubmit="return confirm('Delete this request?')">@csrf @method('DELETE')<button class="btn-ghost w-full min-h-0 py-2 !text-red-700 dark:!text-red-300">Delete</button></form>
                                <form method="POST" action="{{ route('messages.accept', $active) }}" class="flex-1">@csrf<button class="btn-primary w-full min-h-0 py-2">Accept</button></form>
                            </div>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('messages.store', $active) }}" enctype="multipart/form-data" class="dm-composer" data-dm-form
                          data-poll-url="{{ route('messages.poll', $active) }}" data-me="{{ $me->id }}">
                        @csrf
                        {{-- Photos and files waiting to be sent --}}
                        <div class="dm-tray" data-dm-tray hidden></div>

                        {{-- Voice recorder (replaces the box while recording) --}}
                        <div class="dm-recorder" data-dm-recorder hidden>
                            <button type="button" class="dm-tool dm-tool--danger" data-dm-record-cancel aria-label="Delete recording">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg>
                            </button>
                            <span class="dm-recorder__dot" aria-hidden="true"></span>
                            <span class="dm-recorder__time" data-dm-record-time>0:00</span>
                            <span class="dm-recorder__bars" aria-hidden="true" data-dm-record-bars></span>
                            <button type="button" class="dm-compose__send" data-dm-record-send>Send</button>
                        </div>

                        <div class="dm-compose" data-dm-compose>
                            <input type="file" accept="image/*" multiple class="sr-only" id="dm-photos" data-dm-photos tabindex="-1">
                            <input type="file" accept=".pdf,.epub,.txt,.md,.doc,.docx,.odt,.rtf,.csv,.xls,.xlsx,.ppt,.pptx,.zip" multiple class="sr-only" id="dm-files" data-dm-files tabindex="-1">
                            <label for="dm-photos" class="dm-tool" title="Send photos" aria-label="Send photos">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="4"/><circle cx="8.5" cy="9" r="1.8"/><path d="m21 15-4.5-4.5L6 21"/></svg>
                            </label>
                            <label for="dm-files" class="dm-tool" title="Send a file" aria-label="Send a file">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m20.5 11.5-8.3 8.3a5 5 0 0 1-7.1-7.1l8.3-8.3a3.4 3.4 0 0 1 4.8 4.8l-8.3 8.3a1.7 1.7 0 0 1-2.4-2.4l7.6-7.6"/></svg>
                            </label>
                            <label for="dm-body" class="sr-only">Message</label>
                            <textarea id="dm-body" name="body" rows="1" maxlength="2000" placeholder="Message…" autocomplete="off" data-dm-input></textarea>
                            <button type="button" class="dm-tool" data-dm-record title="Record a voice message" aria-label="Record a voice message">
                                <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="9" y="3" width="6" height="11" rx="3"/><path d="M5 11a7 7 0 0 0 14 0M12 18v3"/></svg>
                            </button>
                            <button type="submit" class="dm-compose__send" data-dm-send hidden>Send</button>
                        </div>
                    </form>
                @else
                    <div class="dm-empty">
                        <span class="dm-empty__icon" aria-hidden="true">
                            <svg viewBox="0 0 24 24"><path d="M21.5 3.5 10.6 13.4M21.5 3.5 14.6 21l-4-7.6-7.6-4 18.5-5.9Z"/></svg>
                        </span>
                        <h2 class="mt-4 font-serif text-2xl font-semibold text-ink dark:text-[#F4E9E2]">Your messages</h2>
                        <p class="mt-1 text-sm text-tan">Send private messages and posts to other readers.</p>
                        <button type="button" class="btn-primary mt-5" data-dm-new>Send message</button>
                    </div>
                @endif
            </section>
        </div>
    </div>

    {{-- New message: search a reader --}}
    <dialog class="dm-picker" data-dm-picker aria-labelledby="dm-picker-title">
        <header class="flex items-center gap-2 border-b border-warmborder px-3 py-2.5 dark:border-[#4B3035]">
            <button type="button" class="ig-icon !h-9 !w-9" data-dm-picker-close aria-label="Close"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg></button>
            <h2 id="dm-picker-title" class="flex-1 text-center font-semibold">New message</h2>
            <span class="w-9" aria-hidden="true"></span>
        </header>
        <div class="flex items-center gap-2 border-b border-warmborder px-4 py-2 dark:border-[#4B3035]">
            <label for="dm-picker-search" class="text-sm font-semibold">To:</label>
            <input id="dm-picker-search" type="search" placeholder="Search…" autocomplete="off" class="min-w-0 flex-1 border-0 bg-transparent px-0 text-sm focus:ring-0" data-dm-picker-input>
        </div>
        <div class="max-h-80 min-h-40 overflow-y-auto p-2" data-dm-picker-results></div>
    </dialog>
</x-app-layout>
