{{-- Conversation list items. Expects $conversations, $tab, $activeId and $me. --}}
@forelse ($conversations as $conversation)
    @php $person = $conversation->other($me); @endphp
    @continue(! $person)
    <li>
        <a href="{{ route('messages.show', $conversation) }}" @class(['dm-item', 'is-active' => $activeId === $conversation->id, 'is-unread' => $conversation->unread && $activeId !== $conversation->id])>
            <x-avatar :src="$person->profile_photo_url" :name="$person->name" size="reader" class="!ring-0 !ring-offset-0" />
            <span class="min-w-0 flex-1">
                <span class="dm-item__name">{{ $person->name }}</span>
                <span class="dm-item__preview">
                    <span class="truncate">{{ $conversation->latestMessage?->preview($me) }}</span>
                    <span class="shrink-0">· {{ $conversation->last_message_at?->diffForHumans(short: true) }}</span>
                </span>
            </span>
            @if ($conversation->unread && $activeId !== $conversation->id)<span class="dm-item__dot" aria-label="Unread"></span>@endif
        </a>
    </li>
@empty
    <li class="px-6 py-10 text-center text-sm text-tan">
        {{ $tab === 'requests' ? 'No message requests.' : 'No messages yet.' }}
    </li>
@endforelse
