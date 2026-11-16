<a href="{{ route('clubs.show', $club) }}" class="club-card">
    <div class="flex items-start gap-3">
        <span class="club-emoji" aria-hidden="true">{{ $club->emoji }}</span>
        <div class="min-w-0 flex-1">
            <p class="truncate font-serif text-lg font-semibold text-ink dark:text-[#f4e9e2]">{{ $club->name }}</p>
            <p class="text-xs text-tan">{{ $club->joined_members_count }} {{ Str::plural('member', $club->joined_members_count) }} · {{ $club->is_private ? 'Invite only' : 'Open' }}</p>
        </div>
    </div>
    @if ($club->description)
        <p class="mt-3 line-clamp-2 text-sm text-walnut dark:text-[#d4c2b9]">{{ $club->description }}</p>
    @endif
    <div class="mt-auto flex items-center gap-3 border-t border-dashed border-warmborder pt-3 dark:border-[#4B3035]">
        @if ($club->currentRound)
            <x-book-cover :book="$club->currentRound->book" class="w-9 shrink-0" />
            <p class="min-w-0 text-sm"><span class="block text-xs text-tan">Reading now</span><span class="block truncate font-semibold text-ink dark:text-[#f4e9e2]">{{ $club->currentRound->book->title }}</span></p>
        @else
            <p class="text-sm text-tan">Choosing the next book…</p>
        @endif
        @if ($discover ?? false)<span class="ml-auto shrink-0 text-sm font-semibold text-burgundy dark:text-[#f0a8b4]">View →</span>@endif
    </div>
</a>
