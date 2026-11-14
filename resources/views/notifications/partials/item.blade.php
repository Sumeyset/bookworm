@php
    $data = $notification->data;
    $icon = ['follow' => '👋', 'like' => '❤️', 'comment' => '💬', 'review' => '📖', 'goal' => '🎯', 'buddy' => '🤝', 'reading' => '📖', 'finished' => '✅', 'rated' => '⭐', 'reviewed' => '📝'][$data['kind'] ?? ''] ?? '🔔';
@endphp
<a href="{{ route('notifications.open', $notification->id) }}" class="focus-ring flex items-start gap-3 rounded-2xl px-3 py-2.5 transition hover:bg-parchment/70 dark:hover:bg-white/5 {{ $notification->read_at ? '' : 'bg-rosetint/40 dark:bg-white/[0.04]' }}">
    <span class="relative shrink-0">
        @if (! empty($data['actor_name']))
            <x-avatar :src="$data['actor_photo'] ?? null" :name="$data['actor_name']" size="sm" />
        @else
            <span class="flex h-9 w-9 items-center justify-center rounded-full bg-parchment text-lg dark:bg-white/10">{{ $icon }}</span>
        @endif
        <span class="absolute -bottom-1 -right-1 text-sm" aria-hidden="true">{{ ! empty($data['actor_name']) ? $icon : '' }}</span>
    </span>
    <span class="min-w-0 flex-1">
        <span class="block text-sm leading-5 text-ink dark:text-[#F4E9E2]">{{ $data['message'] ?? '' }}</span>
        <span class="mt-0.5 block text-xs text-tan">{{ $notification->created_at->diffForHumans() }}</span>
    </span>
    @unless ($notification->read_at)<span class="mt-1.5 h-2 w-2 shrink-0 rounded-full bg-burgundy dark:bg-[#F0A8B4]" aria-label="Unread"></span>@endunless
</a>
