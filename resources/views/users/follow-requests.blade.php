<x-app-layout>
    <x-slot name="title">Follow requests · Bookworm</x-slot>
    <x-slot name="header">
        <x-page-header eyebrow="Private account" title="Follow requests" subtitle="Readers who asked to see your shelf and posts." />
    </x-slot>

    <div class="page-section">
        <div class="bw-card mx-auto max-w-2xl divide-y divide-warmborder p-2 dark:divide-[#4B3035]">
            @forelse ($requests as $person)
                <div class="flex flex-wrap items-center gap-3 p-3">
                    <a href="{{ route('users.show', $person->username) }}"><x-avatar :src="$person->profile_photo_url" :name="$person->name" size="sm" /></a>
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('users.show', $person->username) }}" class="block truncate font-semibold text-ink hover:underline dark:text-[#F4E9E2]">{{ $person->name }}</a>
                        <p class="text-xs text-tan">{{ '@' . $person->username }} · asked {{ \Illuminate\Support\Carbon::parse($person->pivot->created_at)->diffForHumans() }}</p>
                    </div>
                    <form method="POST" action="{{ route('follow-requests.approve', $person->username) }}">@csrf<button class="btn-primary min-h-0 py-2">Accept</button></form>
                    <form method="POST" action="{{ route('follow-requests.decline', $person->username) }}">@csrf @method('DELETE')<button class="btn-ghost min-h-0 py-2">Decline</button></form>
                </div>
            @empty
                <p class="p-8 text-center text-walnut dark:text-[#d4c2b9]">No requests right now.</p>
            @endforelse
        </div>
    </div>
</x-app-layout>
