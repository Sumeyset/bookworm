<x-app-layout>
    <x-slot name="title">{{ $club->name }} · Book club</x-slot>

    @php
        $me = auth()->user();
        $today = today();
        $currentSection = $round?->sections->first(fn ($s) => $s->due_on->gte($today)) ?? $round?->sections->last();
        $total = $myItem?->total_pages ?: $round?->book->page_count;
    @endphp

    <div class="page-section space-y-6">
        <a href="{{ route('clubs.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-burgundy hover:underline dark:text-[#E9A2AE]">&larr; All clubs</a>

        {{-- Club header --}}
        <section class="club-hero">
            <div class="flex flex-wrap items-start gap-5">
                <span class="club-emoji club-emoji--big" aria-hidden="true">{{ $club->emoji }}</span>
                <div class="min-w-0 flex-1">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#F2C38B]">Book club · {{ $club->is_private ? 'invite only' : 'open to everyone' }}</p>
                    <h1 class="mt-1 font-display text-5xl font-bold leading-none sm:text-6xl">{{ $club->name }}</h1>
                    @if ($club->description)<p class="mt-3 max-w-2xl text-[#F1D9C4]">{{ $club->description }}</p>@endif
                    <p class="mt-3 text-sm text-[#F1D9C4]/80">Run by {{ $club->owner->id === $me->id ? 'you' : $club->owner->name }} · {{ $members->where('joined', true)->count() }} {{ Str::plural('member', $members->where('joined', true)->count()) }}</p>
                </div>
                <div class="flex shrink-0 flex-wrap gap-2">
                    @if (! $joined)
                        <form method="POST" action="{{ route('clubs.join', $club) }}">@csrf<button class="rounded-full bg-[#FFF8EF] px-5 py-2.5 text-sm font-semibold text-burgundy-deep">Join club</button></form>
                    @endif
                </div>
            </div>
        </section>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
            <div class="min-w-0 space-y-6">
                @if ($round)
                    {{-- Current book and schedule --}}
                    <section class="bw-card p-5 sm:p-7" aria-labelledby="now-reading">
                        <div class="flex gap-5">
                            <x-book-cover :book="$round->book" class="w-24 shrink-0 sm:w-28" />
                            <div class="min-w-0 flex-1">
                                <p class="eyebrow">Reading now · since {{ $round->starts_on->format('M j') }}</p>
                                <h2 id="now-reading" class="mt-1 font-display text-4xl font-bold leading-none text-ink dark:text-[#F4E9E2]">{{ $round->book->title }}</h2>
                                <p class="mt-1 text-sm text-walnut dark:text-[#d4c2b9]">{{ $round->book->author_name }}</p>
                                @if ($currentSection)
                                    <p class="mt-3 inline-flex rounded-full bg-parchment px-3 py-1 text-sm font-semibold text-walnut dark:bg-white/10 dark:text-[#e3d3ca]">
                                        This week: pages {{ $currentSection->from_page }}–{{ $currentSection->to_page }} by {{ $currentSection->due_on->format('D, M j') }}
                                    </p>
                                @endif
                            </div>
                        </div>

                        {{-- Everyone's bookmark on the schedule --}}
                        @php $last = max(1, (int) $round->sections->last()?->to_page); @endphp
                        <div class="club-track mt-8" aria-label="Where everyone is">
                            @foreach ($round->sections as $section)
                                <span class="club-track__mark" style="left: {{ $section->to_page / $last * 100 }}%" title="{{ $section->title }} ends at page {{ $section->to_page }}"></span>
                            @endforeach
                            @foreach ($members->where('joined', true) as $member)
                                <span class="club-track__reader" style="left: {{ max(2, min(98, $member->page === PHP_INT_MAX ? 100 : $member->page / $last * 100)) }}%; z-index: {{ $member->id === $me->id ? 3 : 2 }}" title="{{ $member->name }}{{ $member->page === PHP_INT_MAX ? ' · finished' : ' · p. ' . $member->page }}">
                                    <x-avatar :src="$member->profile_photo_url" :name="$member->name" size="sm" class="{{ $member->id === $me->id ? '!ring-burgundy' : 'ring-gold' }} !h-7 !w-7 ring-2 !text-xs" />
                                </span>
                            @endforeach
                        </div>
                        <div class="mt-5 flex justify-between text-[11px] font-semibold uppercase tracking-wider text-tan"><span>Page 1</span><span>Page {{ $last }}</span></div>
                    </section>

                    {{-- One discussion per section --}}
                    <section aria-labelledby="schedule-heading" class="space-y-3">
                        <h2 id="schedule-heading" class="font-display text-3xl font-bold text-ink dark:text-[#F4E9E2]">Schedule & discussion</h2>
                        <p class="text-sm text-tan">Each part opens once your bookmark reaches its last page, so nobody spoils anything.</p>
                        @foreach ($round->sections as $section)
                            @php
                                $state = $section->due_on->lt($today) ? 'past' : ($currentSection && $section->is($currentSection) ? 'now' : 'next');
                                $reached = $myPage >= $section->to_page;
                            @endphp
                            <article class="club-section club-section--{{ $state }}" x-data="{ open: {{ $state === 'now' ? 'true' : 'false' }}, peek: false }">
                                <button type="button" class="club-section__head" @click="open = !open" :aria-expanded="open">
                                    <span class="club-section__num">{{ $section->number }}</span>
                                    <span class="min-w-0 flex-1 text-left">
                                        <span class="block font-serif text-lg font-semibold text-ink dark:text-[#F4E9E2]">{{ $section->title }} · pages {{ $section->from_page }}–{{ $section->to_page }}</span>
                                        <span class="block text-xs text-tan">
                                            {{ $state === 'past' ? 'Was due' : 'Due' }} {{ $section->due_on->format('D, M j') }}
                                            · {{ $section->posts->count() }} {{ Str::plural('message', $section->posts->count()) }}
                                            · {{ $members->where('joined', true)->filter(fn ($m) => $m->page >= $section->to_page)->count() }}/{{ $members->where('joined', true)->count() }} there
                                        </span>
                                    </span>
                                    <span class="shrink-0 text-sm">{{ $reached ? '✅' : '🔒' }}</span>
                                </button>

                                <div x-show="open" x-cloak class="club-section__body">
                                    <div x-show="!{{ $reached ? 'true' : 'false' }} && !peek" class="club-section__lock">
                                        <p class="font-semibold text-ink dark:text-[#F4E9E2]">🙈 Read to page {{ $section->to_page }} to open this part</p>
                                        <p class="text-sm text-tan">You’re on page {{ $myPage === PHP_INT_MAX ? 'the end' : $myPage }}. Move your bookmark when you get there.</p>
                                        <button type="button" class="mt-2 text-sm font-semibold text-burgundy underline underline-offset-2 dark:text-[#F0A8B4]" @click="peek = true">Peek anyway (spoilers!)</button>
                                    </div>

                                    <div x-show="{{ $reached ? 'true' : 'false' }} || peek" @if (! $reached) x-cloak @endif>
                                        @if ($joined)
                                            <div class="mb-3 flex justify-end">
                                                <button type="button" class="bw-chip" data-barnaby-questions="{{ route('barnaby.club', [$club, $section]) }}" data-box="#questions-{{ $section->id }}" data-input="#club-message-{{ $section->id }}">
                                                    <x-barnaby-avatar class="h-5 w-5" /> Discussion questions
                                                </button>
                                            </div>
                                            <div id="questions-{{ $section->id }}" class="barnaby-answer mb-4" hidden>
                                                <x-barnaby-avatar class="h-9 w-9 shrink-0" />
                                                <div class="min-w-0 flex-1">
                                                    <div class="barnaby-msg__bubble" data-questions-text></div>
                                                    <p class="mt-1.5 text-xs text-tan" data-questions-note></p>
                                                </div>
                                            </div>
                                        @endif

                                        <div class="space-y-3">
                                            @forelse ($section->posts as $post)
                                                @php $mine = $post->user_id === $me->id; @endphp
                                                <article id="club-post-{{ $post->id }}" class="flex scroll-mt-28 gap-2.5 {{ $mine ? 'flex-row-reverse' : '' }}">
                                                    <x-avatar :src="$post->user->profile_photo_url" :name="$post->user->name" size="sm" class="mt-5" />
                                                    <div class="flex min-w-0 max-w-[80%] flex-col {{ $mine ? 'items-end' : 'items-start' }}">
                                                        <p class="mb-1 px-1 text-xs text-tan">
                                                            <span class="font-semibold text-ink dark:text-[#F4E9E2]">{{ $mine ? 'You' : $post->user->name }}</span> · {{ $post->created_at->diffForHumans(short: true) }}
                                                            @if ($mine || $isOwner)
                                                                <form method="POST" action="{{ route('clubs.posts.destroy', [$club, $post]) }}" class="inline" onsubmit="return confirm('Remove this message?')">@csrf @method('DELETE')<button class="ml-1 hover:text-burgundy hover:underline">remove</button></form>
                                                            @endif
                                                            @unless ($mine)
                                                                <button type="button" data-report="club_post:{{ $post->id }}" class="ml-1 hover:text-burgundy hover:underline">report</button>
                                                            @endunless
                                                        </p>
                                                        <div class="rounded-2xl px-4 py-2.5 shadow-sm {{ $mine ? 'rounded-tr-sm bg-burgundy text-[#FFF8EF] dark:bg-burgundy-dark' : 'rounded-tl-sm bg-paper text-ink dark:bg-raised dark:text-[#F4E9E2]' }}">
                                                            <p class="whitespace-pre-line break-words text-[15px] leading-6">{{ $post->body }}</p>
                                                        </div>
                                                    </div>
                                                </article>
                                            @empty
                                                <p class="py-4 text-center text-sm text-tan">No messages yet — what did you think of this part?</p>
                                            @endforelse
                                        </div>

                                        @if ($joined)
                                            <form method="POST" action="{{ route('clubs.posts.store', [$club, $section]) }}" class="mt-4" x-data="{ body: '' }">
                                                @csrf
                                                <label for="club-message-{{ $section->id }}" class="sr-only">Message about {{ $section->title }}</label>
                                                <textarea id="club-message-{{ $section->id }}" name="body" x-model="body" rows="2" required maxlength="2000" placeholder="Your thoughts on pages {{ $section->from_page }}–{{ $section->to_page }}…" class="bw-input w-full resize-none"></textarea>
                                                <div class="mt-2 flex justify-end"><button class="btn-primary min-h-0 py-2" :disabled="!body.trim()">Send</button></div>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </article>
                        @endforeach
                    </section>
                @else
                    <section class="bw-card p-8 text-center">
                        <p class="text-4xl" aria-hidden="true">📖</p>
                        <h2 class="mt-2 font-display text-3xl font-bold text-ink dark:text-[#F4E9E2]">No book yet</h2>
                        <p class="mt-1 text-walnut dark:text-[#d4c2b9]">{{ $isOwner ? 'Pick the club’s first book and a reading schedule on the right.' : 'The club is choosing its next book.' }}</p>
                    </section>
                @endif

                @if ($past->isNotEmpty())
                    <section aria-labelledby="past-books" class="bw-card p-5">
                        <h2 id="past-books" class="font-serif text-xl font-semibold text-ink dark:text-[#F4E9E2]">Books we’ve read</h2>
                        <ul class="mt-3 flex flex-wrap gap-3">
                            @foreach ($past as $old)
                                <li class="w-20 text-center"><a href="{{ route('books.show', $old->book) }}"><x-book-cover :book="$old->book" class="w-20" /><span class="mt-1 block truncate text-xs text-walnut dark:text-[#d4c2b9]">{{ $old->book->title }}</span></a></li>
                            @endforeach
                        </ul>
                    </section>
                @endif
            </div>

            <aside class="space-y-6 lg:sticky lg:top-28 lg:self-start">
                @if ($joined && $round)
                    <section class="bw-card p-5" aria-labelledby="bookmark-heading" x-data="{ page: {{ (int) ($myItem?->current_page ?? 0) }}, total: {{ (int) ($total ?? 0) }} }">
                        <h2 id="bookmark-heading" class="font-serif text-xl font-semibold text-ink dark:text-[#F4E9E2]">Your bookmark</h2>
                        <p class="mt-0.5 text-sm text-tan">Parts of the discussion open as you go.</p>
                        <form method="POST" action="{{ route('books.shelf.update', $round->book) }}" class="mt-4 space-y-3">
                            @csrf
                            <input type="hidden" name="status" :value="total && page >= total ? 'read' : 'reading'">
                            <div class="flex items-center gap-2">
                                <label for="club-page" class="sr-only">Current page</label>
                                <input id="club-page" type="number" name="current_page" min="0" :max="total || null" x-model.number="page" class="bw-input w-24 py-2 text-center text-lg font-semibold">
                                <span class="text-sm text-tan">of</span>
                                <input type="number" name="total_pages" min="1" x-model.number="total" class="bw-input w-24 py-2 text-center" aria-label="Total pages">
                            </div>
                            <input type="range" min="0" :max="total || 500" x-model.number="page" class="w-full accent-burgundy" aria-label="Bookmark">
                            <button class="btn-primary w-full">Save bookmark</button>
                        </form>
                    </section>
                @endif

                <section class="bw-card p-5" aria-labelledby="members-heading">
                    <h2 id="members-heading" class="font-serif text-xl font-semibold text-ink dark:text-[#F4E9E2]">Members <span class="text-sm font-normal text-tan">{{ $members->where('joined', true)->count() }}</span></h2>
                    <ul class="mt-3 space-y-3">
                        @foreach ($members as $member)
                            <li class="flex items-center gap-3">
                                <a href="{{ route('users.show', $member->username) }}" class="shrink-0"><x-avatar :src="$member->profile_photo_url" :name="$member->name" size="sm" /></a>
                                <div class="min-w-0 flex-1">
                                    <p class="flex justify-between gap-2 text-sm">
                                        <span class="truncate font-semibold text-ink dark:text-[#F4E9E2]">{{ $member->id === $me->id ? 'You' : $member->name }}{{ $club->owner_id === $member->id ? ' 👑' : '' }}</span>
                                        <span class="shrink-0 text-xs text-tan">{{ ! $member->joined ? 'Invited' : ($round ? ($member->page === PHP_INT_MAX ? 'Finished 🎉' : ($member->page ? 'p. ' . $member->page : 'Starting')) : '') }}</span>
                                    </p>
                                    @if ($round && $member->joined)
                                        <div class="mt-1 h-1.5 rounded-full bg-parchment dark:bg-white/10"><div class="h-full rounded-full bg-gradient-to-r from-burgundy to-[#b84560]" style="width: {{ $member->percent }}%"></div></div>
                                    @endif
                                </div>
                                @if ($isOwner && $member->id !== $me->id)
                                    <form method="POST" action="{{ route('clubs.members.remove', [$club, $member]) }}" onsubmit="return confirm('Remove {{ $member->name }} from the club?')">@csrf @method('DELETE')<button class="text-xs text-tan hover:text-burgundy" aria-label="Remove {{ $member->name }}">✕</button></form>
                                @endif
                            </li>
                        @endforeach
                    </ul>

                    @if ($joined)
                        <div class="mt-5 border-t border-warmborder pt-4 dark:border-[#4B3035]" x-data="{ open: false }">
                            @if ($invitable->isNotEmpty())
                                <button type="button" @click="open = !open" class="btn-ghost w-full" :aria-expanded="open">+ Invite friends</button>
                                <form x-show="open" x-cloak method="POST" action="{{ route('clubs.invite', $club) }}" class="mt-3 space-y-2">
                                    @csrf
                                    <div class="max-h-52 space-y-1 overflow-y-auto">
                                        @foreach ($invitable as $friend)
                                            <label class="flex cursor-pointer items-center gap-3 rounded-xl p-2 hover:bg-parchment/60 dark:hover:bg-white/5">
                                                <input type="checkbox" name="invitees[]" value="{{ $friend->id }}" class="rounded border-warmborder text-burgundy focus:ring-burgundy/30">
                                                <x-avatar :src="$friend->profile_photo_url" :name="$friend->name" size="sm" class="!h-7 !w-7 !text-xs" />
                                                <span class="truncate text-sm font-semibold text-ink dark:text-[#F4E9E2]">{{ $friend->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <button class="btn-primary w-full min-h-0 py-2">Send invitations</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('clubs.leave', $club) }}" class="mt-3 text-center" onsubmit="return confirm('{{ $isOwner ? 'Leave the club? It will pass to the longest-standing member (or close if you are the only one).' : 'Leave this club?' }}')">
                                @csrf @method('DELETE')
                                <button class="text-sm font-semibold text-tan hover:text-burgundy hover:underline">Leave club</button>
                            </form>
                        </div>
                    @endif
                </section>

                @if ($isOwner)
                    <section class="bw-card p-5" aria-labelledby="owner-heading" x-data="{ book: '{{ $shelfBooks->first()?->id }}', pages: {{ (int) ($shelfBooks->first()?->page_count ?: 300) }}, sections: 4, every: 7, counts: @js($shelfBooks->pluck('page_count', 'id')) }">
                        <h2 id="owner-heading" class="font-serif text-xl font-semibold text-ink dark:text-[#F4E9E2]">{{ $round ? 'Next book' : 'Pick the first book' }}</h2>
                        @if ($shelfBooks->isEmpty())
                            <p class="mt-2 text-sm text-walnut dark:text-[#d4c2b9]">Add the book to your shelf first, then pick it here. <a href="{{ route('books.index') }}" class="font-semibold text-burgundy underline dark:text-[#F0A8B4]">Find a book</a></p>
                        @else
                            <form method="POST" action="{{ route('clubs.rounds.store', $club) }}" class="mt-3 space-y-3" @if ($round) onsubmit="return confirm('Start a new book? The current one moves to “Books we’ve read”.')" @endif>
                                @csrf
                                <div>
                                    <label for="round-book" class="block text-sm font-semibold text-ink dark:text-[#F4E9E2]">Book (from your shelf)</label>
                                    <select id="round-book" name="book_id" x-model="book" @change="pages = counts[book] || pages" class="bw-input mt-1 w-full">
                                        @foreach ($shelfBooks as $book)
                                            <option value="{{ $book->id }}">{{ Str::limit($book->title, 50) }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="grid grid-cols-3 gap-2">
                                    <label class="text-xs font-semibold text-tan">Pages<input type="number" name="pages" min="10" max="5000" x-model.number="pages" class="bw-input mt-1 w-full py-1.5"></label>
                                    <label class="text-xs font-semibold text-tan">Parts<input type="number" name="sections" min="1" max="20" x-model.number="sections" class="bw-input mt-1 w-full py-1.5"></label>
                                    <label class="text-xs font-semibold text-tan">Every … days<input type="number" name="every_days" min="1" max="30" x-model.number="every" class="bw-input mt-1 w-full py-1.5"></label>
                                </div>
                                <label class="block text-xs font-semibold text-tan">Start on<input type="date" name="starts_on" value="{{ today()->toDateString() }}" class="bw-input mt-1 w-full py-1.5"></label>
                                <p class="text-xs text-tan" x-text="`${sections} parts of about ${Math.round(pages / Math.max(1, sections))} pages, one every ${every} days — about ${sections * every} days in all.`"></p>
                                <button class="btn-primary w-full">{{ $round ? 'Start next book' : 'Start reading' }}</button>
                            </form>
                        @endif
                        @if ($round)
                            <form method="POST" action="{{ route('clubs.rounds.finish', $club) }}" class="mt-3 text-center">@csrf<button class="text-sm font-semibold text-tan hover:text-burgundy hover:underline">Mark “{{ Str::limit($round->book->title, 30) }}” finished</button></form>
                        @endif

                        <details class="mt-4 border-t border-warmborder pt-3 dark:border-[#4B3035]">
                            <summary class="cursor-pointer text-sm font-semibold text-walnut dark:text-[#d4c2b9]">Club settings</summary>
                            <form method="POST" action="{{ route('clubs.update', $club) }}" class="mt-3 space-y-3">
                                @csrf @method('PATCH')
                                <input name="name" value="{{ $club->name }}" maxlength="80" required class="bw-input w-full" aria-label="Club name">
                                <select name="emoji" class="bw-input w-full" aria-label="Club sign">
                                    @foreach ($emojis as $emoji)<option value="{{ $emoji }}" @selected($club->emoji === $emoji)>{{ $emoji }}</option>@endforeach
                                </select>
                                <textarea name="description" rows="2" maxlength="500" class="bw-input w-full" aria-label="Description">{{ $club->description }}</textarea>
                                <label class="flex items-center gap-2 text-sm text-walnut dark:text-[#d4c2b9]"><input type="checkbox" name="is_private" value="1" class="rounded border-warmborder text-burgundy" @checked($club->is_private)> Invite only</label>
                                <button class="btn-ghost w-full">Save</button>
                            </form>
                        </details>
                    </section>
                @endif
            </aside>
        </div>
    </div>
</x-app-layout>
