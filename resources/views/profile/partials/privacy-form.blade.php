@php
    $blocked = $user->blockedUsers()->orderBy('name')->get();
    $requestCount = $user->followRequests()->count();
@endphp
<form method="POST" action="{{ route('settings.privacy') }}" class="space-y-4">
    @csrf
    @method('PATCH')
    <label class="flex items-start gap-3">
        <input type="checkbox" name="is_private" value="1" class="mt-1 rounded border-warmborder text-burgundy focus:ring-burgundy" @checked($user->is_private)>
        <span>
            <span class="block font-semibold text-ink dark:text-[#F4E9E2]">🔒 Private account</span>
            <span class="block text-sm text-walnut dark:text-[#d4c2b9]">Only followers you approve see your shelf, posts, badges and year in books. Your name, photo and bio stay visible so friends can find you. Your public link is turned off.</span>
        </span>
    </label>
    <label class="flex items-start gap-3">
        <input type="checkbox" name="hide_activity" value="1" class="mt-1 rounded border-warmborder text-burgundy focus:ring-burgundy" @checked($user->hide_activity)>
        <span>
            <span class="block font-semibold text-ink dark:text-[#F4E9E2]">🙈 Hide my reading activity</span>
            <span class="block text-sm text-walnut dark:text-[#d4c2b9]">Followers won’t be told when you start, finish, rate or review a book.</span>
        </span>
    </label>
    <div class="flex items-center gap-4">
        <button class="btn-primary">Save privacy</button>
        @if (session('status') === 'privacy-updated')<p class="text-sm text-tan">Saved.</p>@endif
    </div>
</form>

@if ($user->is_private || $requestCount)
    <a href="{{ route('follow-requests.index') }}" class="mt-6 flex items-center justify-between rounded-2xl bg-parchment/70 px-4 py-3 text-sm font-semibold text-ink hover:bg-parchment dark:bg-white/5 dark:text-[#F4E9E2]">
        Follow requests <span class="rounded-full bg-burgundy px-2.5 py-0.5 text-xs text-[#FFF8EF]">{{ $requestCount }}</span>
    </a>
@endif

<div class="mt-8 border-t border-warmborder pt-6 dark:border-[#4B3035]">
    <p class="font-semibold text-ink dark:text-[#F4E9E2]">Blocked readers</p>
    @if ($blocked->isEmpty())
        <p class="mt-1 text-sm text-tan">You haven’t blocked anyone. You can block a reader from their profile or a chat.</p>
    @else
        <ul class="mt-3 space-y-2">
            @foreach ($blocked as $person)
                <li class="flex items-center gap-3">
                    <x-avatar :src="$person->profile_photo_url" :name="$person->name" size="sm" />
                    <span class="min-w-0 flex-1 truncate text-sm"><b class="text-ink dark:text-[#F4E9E2]">{{ $person->name }}</b> <span class="text-tan">{{ '@' . $person->username }}</span></span>
                    <form method="POST" action="{{ route('users.unblock', $person->username) }}">@csrf @method('DELETE')<button class="btn-ghost min-h-0 py-1.5 text-xs">Unblock</button></form>
                </li>
            @endforeach
        </ul>
    @endif
</div>
