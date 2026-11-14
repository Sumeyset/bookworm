<x-app-layout>
    <x-slot name="title">Buddy reads · Bookworm</x-slot>
    <x-slot name="header">
        <x-page-header eyebrow="Read together" title="Buddy reads" subtitle="Pick a book, invite a few readers you follow, and move your bookmarks along side by side — spoiler-free." />
    </x-slot>

    <div class="page-section grid gap-8 lg:grid-cols-[minmax(0,1fr)_24rem]">
        <div class="min-w-0 space-y-8">
            @if ($invites->isNotEmpty())
                <section aria-labelledby="invites-heading">
                    <h2 id="invites-heading" class="font-display text-4xl font-bold text-ink dark:text-[#F4E9E2]">Invitations</h2>
                    <div class="mt-4 space-y-3">
                        @foreach ($invites as $read)
                            <article class="bw-card flex items-center gap-4 p-4">
                                <x-book-cover :book="$read->book" class="w-14 shrink-0" />
                                <div class="min-w-0 flex-1">
                                    <p class="truncate font-serif text-lg font-semibold text-ink dark:text-[#F4E9E2]">{{ $read->title ?: $read->book->title }}</p>
                                    <p class="text-sm text-tan">{{ $read->members->count() }} {{ Str::plural('reader', $read->members->count()) }} invited</p>
                                </div>
                                <form method="POST" action="{{ route('buddy.join', $read) }}">@csrf<button class="btn-primary">Join</button></form>
                            </article>
                        @endforeach
                    </div>
                </section>
            @endif

            <section aria-labelledby="active-heading">
                <h2 id="active-heading" class="font-display text-4xl font-bold text-ink dark:text-[#F4E9E2]">Reading together</h2>
                @if ($active->isEmpty())
                    <x-empty-state class="mt-4" title="No buddy reads yet" message="Start one on the right — it’s more fun to reach the ending together." />
                @else
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        @foreach ($active as $read)
                            <a href="{{ route('buddy.show', $read) }}" class="bw-card group flex gap-4 p-4 transition hover:-translate-y-0.5">
                                <x-book-cover :book="$read->book" class="w-16 shrink-0" />
                                <div class="min-w-0 flex-1">
                                    <p class="line-clamp-2 font-serif text-lg font-semibold leading-tight text-ink group-hover:text-burgundy dark:text-[#F4E9E2]">{{ $read->title ?: $read->book->title }}</p>
                                    <div class="mt-3 flex -space-x-2">
                                        @foreach ($read->members->take(6) as $member)
                                            <x-avatar :src="$member->profile_photo_url" :name="$member->name" size="sm" class="ring-2 ring-paper dark:ring-raised" />
                                        @endforeach
                                    </div>
                                    <p class="mt-2 text-xs text-tan">Updated {{ $read->updated_at->diffForHumans() }}</p>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </section>
        </div>

        <aside class="lg:sticky lg:top-28 lg:self-start">
            <form method="POST" action="{{ route('buddy.store') }}" class="bw-card space-y-4 p-6">
                @csrf
                <h2 class="font-display text-4xl font-bold text-ink dark:text-[#F4E9E2]">Start a buddy read</h2>
                <div>
                    <x-input-label for="buddy-book" value="Book" />
                    <select id="buddy-book" name="book_id" required class="bw-input mt-1 w-full text-sm">
                        <option value="">Choose from your reading / want-to-read…</option>
                        @foreach ($shelfBooks as $book)
                            <option value="{{ $book->id }}" @selected(old('book_id', $preselectBook) == $book->id)>{{ $book->title }}</option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('book_id')" class="mt-1" />
                </div>
                <div>
                    <x-input-label for="buddy-title" value="Name (optional)" />
                    <input id="buddy-title" name="title" maxlength="120" value="{{ old('title') }}" placeholder="e.g. Winter Austen club" class="bw-input mt-1 w-full text-sm">
                </div>
                <fieldset>
                    <legend class="text-sm font-medium text-ink dark:text-[#F4E9E2]">Invite readers you follow</legend>
                    @if ($friends->isEmpty())
                        <p class="mt-2 text-sm text-tan">Follow a few readers first — <a href="{{ route('users.index') }}" class="font-semibold text-burgundy underline">find readers</a>.</p>
                    @else
                        <div class="mt-2 max-h-56 space-y-1 overflow-y-auto">
                            @foreach ($friends as $friend)
                                <label class="flex cursor-pointer items-center gap-3 rounded-xl p-2 hover:bg-parchment/60 dark:hover:bg-white/5">
                                    <input type="checkbox" name="invitees[]" value="{{ $friend->id }}" @checked(in_array($friend->id, old('invitees', []))) class="rounded border-warmborder text-burgundy focus:ring-burgundy/30">
                                    <x-avatar :src="$friend->profile_photo_url" :name="$friend->name" size="sm" />
                                    <span class="text-sm font-semibold text-ink dark:text-[#F4E9E2]">{{ $friend->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    @endif
                    <x-input-error :messages="$errors->get('invitees')" class="mt-1" />
                </fieldset>
                <button class="btn-primary w-full" @disabled($friends->isEmpty())>Send invitations</button>
            </form>
        </aside>
    </div>
</x-app-layout>
