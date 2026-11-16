<x-app-layout>
    <x-slot name="title">Book clubs · Bookworm</x-slot>
    <x-slot name="header">
        <x-page-header eyebrow="Read together" title="Book clubs" subtitle="One book at a time, on a schedule, with a spoiler-safe chat for every part.">
            <x-slot name="actions">
                <a href="#new-club" class="btn-primary">+ Start a club</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="page-section space-y-10">
        @if ($invites->isNotEmpty())
            <section aria-labelledby="club-invites">
                <h2 id="club-invites" class="mb-3 font-display text-3xl font-bold text-ink dark:text-[#f4e9e2]">Invitations</h2>
                <div class="space-y-3">
                    @foreach ($invites as $club)
                        <div class="bw-card flex flex-wrap items-center gap-4 p-4">
                            <span class="club-emoji" aria-hidden="true">{{ $club->emoji }}</span>
                            <div class="min-w-0 flex-1">
                                <p class="font-serif text-lg font-semibold text-ink dark:text-[#f4e9e2]">{{ $club->name }}</p>
                                <p class="text-sm text-tan">{{ $club->joined_members_count }} {{ Str::plural('member', $club->joined_members_count) }}{{ $club->currentRound ? ' · reading “' . $club->currentRound->book->title . '”' : '' }}</p>
                            </div>
                            <form method="POST" action="{{ route('clubs.join', $club) }}">@csrf<button class="btn-primary min-h-0 py-2">Join</button></form>
                            <form method="POST" action="{{ route('clubs.leave', $club) }}">@csrf @method('DELETE')<input type="hidden" name="decline" value="1"><button class="text-sm font-semibold text-tan hover:text-burgundy hover:underline">Not now</button></form>
                        </div>
                    @endforeach
                </div>
            </section>
        @endif

        <section aria-labelledby="my-clubs">
            <h2 id="my-clubs" class="mb-4 font-display text-3xl font-bold text-ink dark:text-[#f4e9e2]">Your clubs</h2>
            @if ($clubs->isEmpty())
                <p class="text-walnut dark:text-[#d4c2b9]">You’re not in a club yet. Join one below, or start your own with friends.</p>
            @else
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($clubs as $club)
                        @include('clubs.partials.card', ['club' => $club])
                    @endforeach
                </div>
            @endif
        </section>

        <section aria-labelledby="discover-clubs">
            <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                <h2 id="discover-clubs" class="font-display text-3xl font-bold text-ink dark:text-[#f4e9e2]">Discover clubs</h2>
                <form method="GET" class="flex gap-2">
                    <label for="club-search" class="sr-only">Search clubs</label>
                    <input id="club-search" type="search" name="q" value="{{ $search }}" placeholder="Search clubs…" class="bw-input w-56 py-2">
                    <button class="btn-ghost min-h-0 py-2">Search</button>
                </form>
            </div>
            @if ($discover->isEmpty())
                <p class="text-walnut dark:text-[#d4c2b9]">{{ $search !== '' ? 'No clubs match “' . $search . '”.' : 'No open clubs yet — be the first to start one!' }}</p>
            @else
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($discover as $club)
                        @include('clubs.partials.card', ['club' => $club, 'discover' => true])
                    @endforeach
                </div>
            @endif
        </section>

        <section id="new-club" aria-labelledby="new-club-heading" class="bw-card scroll-mt-28 p-6 sm:p-8">
            <h2 id="new-club-heading" class="font-display text-3xl font-bold text-ink dark:text-[#f4e9e2]">Start a club</h2>
            <p class="mt-1 text-sm text-walnut dark:text-[#d4c2b9]">You’ll pick the first book and the reading schedule next.</p>
            <form method="POST" action="{{ route('clubs.store') }}" class="mt-5 grid gap-4 sm:grid-cols-2" x-data="{ emoji: '📚' }">
                @csrf
                <div class="sm:col-span-2">
                    <span class="block text-sm font-semibold text-ink dark:text-[#f4e9e2]">Club sign</span>
                    <div class="mt-2 flex flex-wrap gap-2">
                        @foreach ($emojis as $emoji)
                            <label class="club-emoji-pick" :class="emoji === '{{ $emoji }}' && 'is-picked'">
                                <input type="radio" name="emoji" value="{{ $emoji }}" class="sr-only" x-model="emoji">{{ $emoji }}
                            </label>
                        @endforeach
                    </div>
                </div>
                <div>
                    <label for="club-name" class="block text-sm font-semibold text-ink dark:text-[#f4e9e2]">Name</label>
                    <input id="club-name" name="name" required maxlength="80" class="bw-input mt-1 w-full" placeholder="The Midnight Readers" value="{{ old('name') }}">
                    <x-input-error :messages="$errors->get('name')" class="mt-1" />
                </div>
                <label class="flex items-center gap-3 self-end pb-2">
                    <input type="checkbox" name="is_private" value="1" class="rounded border-warmborder text-burgundy focus:ring-burgundy">
                    <span class="text-sm text-walnut dark:text-[#d4c2b9]"><b class="text-ink dark:text-[#f4e9e2]">Invite only</b> — hidden from Discover</span>
                </label>
                <div class="sm:col-span-2">
                    <label for="club-description" class="block text-sm font-semibold text-ink dark:text-[#f4e9e2]">What’s it about?</label>
                    <textarea id="club-description" name="description" rows="2" maxlength="500" class="bw-input mt-1 w-full" placeholder="Cozy mysteries, one a month, with tea.">{{ old('description') }}</textarea>
                </div>
                <div class="sm:col-span-2"><button class="btn-primary">Create club</button></div>
            </form>
        </section>
    </div>
</x-app-layout>
