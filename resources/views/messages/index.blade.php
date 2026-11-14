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
                        Requests @if ($requestCount)<span class="dm-tabs__count">{{ $requestCount }}</span>@endif
                    </a>
                </nav>

                @if ($tab === 'requests')
                    <p class="px-4 pb-2 text-xs text-tan">Messages from readers you don’t follow. They won’t know you’ve seen them until you reply or accept.</p>
                @endif

                <ul class="dm-list__items">
                    @forelse ($conversations as $conversation)
                        @php $person = $conversation->other($me); @endphp
                        @continue(! $person)
                        <li>
                            <a href="{{ route('messages.show', $conversation) }}" @class(['dm-item', 'is-active' => $active?->id === $conversation->id, 'is-unread' => $conversation->unread])>
                                <x-avatar :src="$person->profile_photo_url" :name="$person->name" size="reader" class="!ring-0 !ring-offset-0" />
                                <span class="min-w-0 flex-1">
                                    <span class="dm-item__name">{{ $person->name }}</span>
                                    <span class="dm-item__preview">
                                        <span class="truncate">{{ $conversation->latestMessage?->preview($me) }}</span>
                                        <span class="shrink-0">· {{ $conversation->last_message_at?->diffForHumans(short: true) }}</span>
                                    </span>
                                </span>
                                @if ($conversation->unread)<span class="dm-item__dot" aria-label="Unread"></span>@endif
                            </a>
                        </li>
                    @empty
                        <li class="px-6 py-10 text-center text-sm text-tan">
                            {{ $tab === 'requests' ? 'No message requests.' : 'No messages yet.' }}
                        </li>
                    @endforelse
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
                        <div class="dm-chat__messages" data-dm-messages>
                            @include('messages.partials.messages', ['messages' => $messages, 'viewer' => $me])
                        </div>
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

                    <form method="POST" action="{{ route('messages.store', $active) }}" class="dm-compose" data-dm-form
                          data-poll-url="{{ route('messages.poll', $active) }}" data-me="{{ $me->id }}">
                        @csrf
                        <label for="dm-body" class="sr-only">Message</label>
                        <textarea id="dm-body" name="body" rows="1" maxlength="2000" required placeholder="Message…" autocomplete="off" data-dm-input></textarea>
                        <button type="submit" class="dm-compose__send" data-dm-send disabled>Send</button>
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
