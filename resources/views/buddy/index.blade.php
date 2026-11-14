<x-app-layout>
    <x-slot name="title">Buddy reads · Bookworm</x-slot>
    <x-slot name="header">
        <x-page-header eyebrow="Read together" title="Buddy reads" subtitle="Pick a book, invite friends, and move your bookmarks along side by side — spoiler-free.">
            <x-slot name="actions">
                <button type="button" class="btn-primary" data-open-buddy-wizard>+ Start a buddy read</button>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="page-section space-y-10">
        {{-- Invitations --}}
        @if ($invites->isNotEmpty())
            <section aria-labelledby="invites-heading">
                <h2 id="invites-heading" class="flex items-center gap-2 font-display text-3xl font-bold text-ink dark:text-[#F4E9E2]">
                    Invitations <span class="rounded-full bg-burgundy px-2.5 py-0.5 font-sans text-sm text-[#FFF8EF]">{{ $invites->count() }}</span>
                </h2>
                <div class="mt-4 grid gap-3 md:grid-cols-2">
                    @foreach ($invites as $read)
                        <article class="bw-card flex gap-4 border-burgundy/30 p-4 ring-1 ring-burgundy/15">
                            <x-book-cover :book="$read->book" class="w-16 shrink-0" />
                            <div class="min-w-0 flex-1">
                                <p class="flex items-center gap-2 text-sm text-tan">
                                    <x-avatar :src="$read->creator->profile_photo_url" :name="$read->creator->name" size="sm" class="!h-6 !w-6 !text-xs" />
                                    <span class="truncate"><b class="text-ink dark:text-[#F4E9E2]">{{ $read->creator->name }}</b> invited you</span>
                                </p>
                                <p class="mt-1.5 line-clamp-2 font-serif text-lg font-semibold leading-tight text-ink dark:text-[#F4E9E2]">{{ $read->title ?: $read->book->title }}</p>
                                <p class="mt-0.5 truncate text-xs text-tan">{{ $read->book->author_name }} · {{ $read->members->count() }} {{ Str::plural('reader', $read->members->count()) }}</p>
                                <div class="mt-3 flex flex-wrap items-center gap-2">
                                    <form method="POST" action="{{ route('buddy.join', $read) }}">@csrf<button class="btn-primary min-h-0 py-2">Join</button></form>
                                    <a href="{{ route('buddy.show', $read) }}" class="btn-ghost min-h-0 py-2">Take a look</a>
                                    <form method="POST" action="{{ route('buddy.leave', $read) }}" class="ml-auto">
                                        @csrf @method('DELETE')
                                        <input type="hidden" name="decline" value="1">
                                        <button class="text-sm font-semibold text-tan hover:text-burgundy hover:underline">Not now</button>
                                    </form>
                                </div>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endif

        {{-- Reading together --}}
        <section aria-labelledby="active-heading">
            <h2 id="active-heading" class="font-display text-3xl font-bold text-ink dark:text-[#F4E9E2]">Reading together</h2>

            @if ($active->isEmpty())
                <div class="bw-card mt-4 overflow-hidden">
                    <div class="grid gap-6 p-6 sm:grid-cols-3 sm:p-8">
                        @foreach ([['📖', 'Pick a book', 'Something you’re reading or want to read next.'], ['👋', 'Invite friends', 'Choose readers you follow — they get a notification.'], ['🔖', 'Read side by side', 'See everyone’s bookmark and chat. Messages from pages you haven’t reached stay hidden.']] as $i => [$icon, $title, $text])
                            <div class="flex gap-3 sm:flex-col">
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl bg-parchment text-2xl dark:bg-white/10" aria-hidden="true">{{ $icon }}</span>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-wider text-tan">Step {{ $i + 1 }}</p>
                                    <p class="font-serif text-lg font-semibold text-ink dark:text-[#F4E9E2]">{{ $title }}</p>
                                    <p class="mt-0.5 text-sm text-walnut dark:text-[#d4c2b9]">{{ $text }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    <div class="border-t border-warmborder bg-parchment/40 px-6 py-4 dark:border-[#4B3035] dark:bg-white/5 sm:px-8">
                        <button type="button" class="btn-primary" data-open-buddy-wizard>Start your first buddy read</button>
                    </div>
                </div>
            @else
                <div class="mt-4 grid gap-4 md:grid-cols-2">
                    @foreach ($active as $read)
                        <a href="{{ route('buddy.show', $read) }}" class="bw-card group flex flex-col gap-4 p-4 transition hover:-translate-y-0.5 hover:shadow-lg">
                            <div class="flex gap-4">
                                <x-book-cover :book="$read->book" class="w-16 shrink-0" />
                                <div class="min-w-0 flex-1">
                                    <p class="line-clamp-2 font-serif text-lg font-semibold leading-tight text-ink group-hover:text-burgundy dark:text-[#F4E9E2] dark:group-hover:text-[#E9A2AE]">{{ $read->title ?: $read->book->title }}</p>
                                    <p class="mt-0.5 truncate text-xs text-tan">{{ $read->title ? $read->book->title . ' · ' : '' }}{{ $read->book->author_name }}</p>
                                    <div class="mt-2 flex items-center gap-2">
                                        <div class="flex -space-x-2">
                                            @foreach ($read->members->take(5) as $member)
                                                <x-avatar :src="$member->profile_photo_url" :name="$member->name" size="sm" class="!h-7 !w-7 !text-xs ring-2 ring-paper dark:ring-raised" />
                                            @endforeach
                                        </div>
                                        <span class="text-xs text-tan">{{ $read->members->count() }} {{ Str::plural('reader', $read->members->count()) }}</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Everyone on one track --}}
                            <div>
                                <div class="relative h-2 rounded-full bg-parchment dark:bg-white/10">
                                    <span class="absolute inset-y-0 left-0 rounded-full bg-gradient-to-r from-burgundy to-[#b84560]" style="width: {{ $read->my_percent }}%"></span>
                                    @foreach ($read->member_progress as $entry)
                                        <span class="absolute top-1/2 h-3.5 w-3.5 -translate-x-1/2 -translate-y-1/2 rounded-full border-2 border-paper bg-gold dark:border-raised" style="left: {{ max(2, min(98, $entry['percent'])) }}%" title="{{ $entry['member']->name }} · {{ $entry['percent'] }}%"></span>
                                    @endforeach
                                </div>
                                <p class="mt-1.5 flex justify-between text-xs text-tan">
                                    <span>You: {{ $read->my_percent === 100 ? 'finished 🎉' : ($read->my_page ? 'page ' . $read->my_page . ' · ' . $read->my_percent . '%' : 'not started') }}</span>
                                    @if ($leader = $read->member_progress->first())
                                        <span>Furthest: {{ Str::before($leader['member']->name, ' ') }} · {{ $leader['percent'] }}%</span>
                                    @endif
                                </p>
                            </div>

                            @if ($read->latestComment)
                                <p class="truncate rounded-xl bg-parchment/60 px-3 py-2 text-sm text-walnut dark:bg-white/5 dark:text-[#d4c2b9]">
                                    <b class="text-ink dark:text-[#F4E9E2]">{{ Str::before($read->latestComment->user->name, ' ') }}:</b>
                                    {{ $read->latestComment->page && $read->latestComment->page > (int) $read->my_page && $read->latestComment->user_id !== auth()->id() ? '🙈 a message from page ' . $read->latestComment->page : Str::limit($read->latestComment->body, 80) }}
                                </p>
                            @else
                                <p class="text-sm text-tan">No messages yet — say hello.</p>
                            @endif
                        </a>
                    @endforeach
                </div>
            @endif
        </section>
    </div>

    {{-- Start a buddy read: 1) book → 2) friends → 3) name & start --}}
    <div x-data="buddyWizard(@js(['books' => $shelfBooks, 'friends' => $friends, 'preselect' => $preselectBook, 'old' => $errors->any() ? ['book' => (int) old('book_id'), 'invitees' => array_map('intval', old('invitees', [])), 'title' => old('title', '')] : null]))"
         x-on:open-buddy-wizard.window="open()" x-on:keydown.escape.window="isOpen = false" x-cloak>
        <div x-show="isOpen" x-transition.opacity class="fixed inset-0 z-[70] bg-black/60 backdrop-blur-sm" @click="isOpen = false" aria-hidden="true"></div>
        <section x-show="isOpen" x-transition.scale.95 role="dialog" aria-modal="true" aria-labelledby="buddy-wizard-title"
                 class="fixed inset-x-2 bottom-2 top-16 z-[71] mx-auto flex max-w-xl flex-col overflow-hidden rounded-3xl bg-paper shadow-2xl dark:bg-raised sm:inset-x-6 sm:bottom-8 sm:top-24">
            <form method="POST" action="{{ route('buddy.store') }}" class="flex min-h-0 flex-1 flex-col" data-scroll-top>
                @csrf
                <input type="hidden" name="book_id" :value="bookId">
                <template x-for="id in invitees" :key="id"><input type="hidden" name="invitees[]" :value="id"></template>
                <input type="hidden" name="title" :value="title">

                <header class="flex items-center gap-2 border-b border-warmborder px-3 py-2.5 dark:border-[#4B3035]">
                    <button type="button" class="ig-icon !h-10 !w-10" @click="step > 1 ? step-- : (isOpen = false)" :aria-label="step > 1 ? 'Back' : 'Close'">
                        <svg x-show="step > 1" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                        <svg x-show="step === 1" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
                    </button>
                    <div class="flex-1 text-center">
                        <h2 id="buddy-wizard-title" class="font-semibold text-ink dark:text-[#F4E9E2]" x-text="['Pick a book', 'Invite friends', 'Ready to start'][step - 1]"></h2>
                        <p class="text-xs text-tan">Step <span x-text="step"></span> of 3</p>
                    </div>
                    <button type="button" x-show="step < 3" @click="step++" :disabled="step === 1 ? !bookId : invitees.length === 0" class="min-h-10 rounded-full px-3 text-sm font-semibold text-burgundy disabled:opacity-40 dark:text-[#E9A2AE]">Next</button>
                    <button type="submit" x-show="step === 3" class="min-h-10 rounded-full bg-burgundy px-4 text-sm font-semibold text-[#FFF8EF] dark:bg-burgundy-dark">Start</button>
                </header>
                <div class="h-1 bg-parchment dark:bg-white/10"><div class="h-full bg-burgundy transition-all dark:bg-burgundy-dark" :style="`width: ${step / 3 * 100}%`"></div></div>

                @if ($errors->any())
                    <div class="border-b border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/40 dark:text-red-200" role="alert">{{ $errors->first() }}</div>
                @endif

                {{-- 1. Book --}}
                <div x-show="step === 1" class="flex min-h-0 flex-1 flex-col">
                    <div class="border-b border-warmborder p-3 dark:border-[#4B3035]">
                        <label for="buddy-book-search" class="sr-only">Search your books</label>
                        <input id="buddy-book-search" type="search" x-model="bookSearch" placeholder="Search your shelf…" autocomplete="off" class="bw-input w-full text-sm">
                    </div>
                    <div class="flex-1 overflow-y-auto p-4">
                        <template x-if="books.length === 0">
                            <div class="py-10 text-center">
                                <p class="font-serif text-xl text-ink dark:text-[#F4E9E2]">Your shelf is empty</p>
                                <p class="mt-1 text-sm text-tan">Add a book first, then come back to read it with friends.</p>
                                <a href="{{ route('books.index') }}" class="btn-primary mt-4">Find a book</a>
                            </div>
                        </template>
                        <ul class="grid grid-cols-3 gap-3 sm:grid-cols-4">
                            <template x-for="book in filteredBooks()" :key="book.id">
                                <li>
                                    <button type="button" @click="bookId = book.id; step = 2" class="group w-full text-left" :aria-pressed="bookId === book.id">
                                        <span class="relative block aspect-[2/3] overflow-hidden rounded-lg shadow-md transition group-hover:-translate-y-0.5" :class="bookId === book.id && 'ring-4 ring-burgundy ring-offset-2 ring-offset-paper dark:ring-offset-raised'">
                                            <template x-if="book.cover"><img :src="book.cover" alt="" class="h-full w-full object-cover" loading="lazy"></template>
                                            <template x-if="!book.cover"><span class="flex h-full items-center justify-center bg-gradient-to-br from-burgundy to-burgundy-deep p-2 text-center font-serif text-xs text-[#FFF8EF]" x-text="book.title"></span></template>
                                        </span>
                                        <span class="mt-1.5 line-clamp-2 block text-xs font-semibold leading-tight text-ink dark:text-[#F4E9E2]" x-text="book.title"></span>
                                        <span class="block truncate text-[11px] text-tan" x-text="book.status"></span>
                                    </button>
                                </li>
                            </template>
                        </ul>
                        <p x-show="books.length && filteredBooks().length === 0" class="py-8 text-center text-sm text-tan">No books on your shelf match that.</p>
                    </div>
                </div>

                {{-- 2. Friends --}}
                <div x-show="step === 2" class="flex min-h-0 flex-1 flex-col">
                    <div class="space-y-2 border-b border-warmborder p-3 dark:border-[#4B3035]">
                        <label for="buddy-friend-search" class="sr-only">Search readers you follow</label>
                        <input id="buddy-friend-search" type="search" x-model="friendSearch" placeholder="Search readers you follow…" autocomplete="off" class="bw-input w-full text-sm">
                        <div class="flex flex-wrap gap-1.5" x-show="invitees.length">
                            <template x-for="friend in selectedFriends()" :key="friend.id">
                                <button type="button" @click="toggle(friend.id)" class="inline-flex items-center gap-1 rounded-full bg-burgundy py-1 pl-3 pr-2 text-xs font-semibold text-[#FFF8EF]" :aria-label="`Remove ${friend.name}`">
                                    <span x-text="friend.name"></span><span aria-hidden="true">×</span>
                                </button>
                            </template>
                        </div>
                    </div>
                    <div class="flex-1 overflow-y-auto p-2">
                        <template x-if="friends.length === 0">
                            <div class="py-10 text-center">
                                <p class="font-serif text-xl text-ink dark:text-[#F4E9E2]">Follow a few readers first</p>
                                <p class="mt-1 text-sm text-tan">You can invite anyone you follow.</p>
                                <a href="{{ route('users.index') }}" class="btn-primary mt-4">Find readers</a>
                            </div>
                        </template>
                        <template x-for="friend in filteredFriends()" :key="friend.id">
                            <button type="button" @click="toggle(friend.id)" class="flex w-full items-center gap-3 rounded-2xl px-3 py-2 text-left transition hover:bg-parchment/70 dark:hover:bg-white/5" :aria-pressed="invitees.includes(friend.id)">
                                <template x-if="friend.photo"><img :src="friend.photo" alt="" class="h-11 w-11 rounded-full object-cover"></template>
                                <template x-if="!friend.photo"><span class="flex h-11 w-11 items-center justify-center rounded-full bg-parchment font-serif font-semibold text-burgundy dark:bg-[#493536] dark:text-[#F4E9E2]" x-text="friend.name.charAt(0).toUpperCase()"></span></template>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-semibold text-ink dark:text-[#F4E9E2]" x-text="friend.name"></span>
                                    <span class="block truncate text-xs text-tan" x-text="'@' + friend.username"></span>
                                </span>
                                <span class="flex h-6 w-6 items-center justify-center rounded-full border-2 transition" :class="invitees.includes(friend.id) ? 'border-burgundy bg-burgundy text-white' : 'border-warmborder dark:border-[#594749]'" aria-hidden="true">
                                    <svg x-show="invitees.includes(friend.id)" class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"><path d="M5 12l5 5 9-10"/></svg>
                                </span>
                            </button>
                        </template>
                    </div>
                </div>

                {{-- 3. Name & start --}}
                <div x-show="step === 3" class="flex-1 space-y-5 overflow-y-auto p-5">
                    <div class="flex items-center gap-4 rounded-2xl bg-parchment/60 p-4 dark:bg-white/5">
                        <template x-if="selectedBook()?.cover"><img :src="selectedBook()?.cover" alt="" class="w-16 rounded-md shadow-md"></template>
                        <div class="min-w-0">
                            <p class="font-serif text-lg font-semibold leading-tight text-ink dark:text-[#F4E9E2]" x-text="selectedBook()?.title"></p>
                            <p class="text-xs text-tan" x-text="selectedBook()?.author"></p>
                            <p class="mt-2 text-sm text-walnut dark:text-[#d4c2b9]">With <b x-text="selectedFriends().map((f) => f.name.split(' ')[0]).join(', ')"></b></p>
                        </div>
                    </div>
                    <div>
                        <label for="buddy-title" class="eyebrow">Give it a name (optional)</label>
                        <input id="buddy-title" x-model="title" maxlength="120" :placeholder="`e.g. ${selectedBook()?.title ?? 'Book'} club`" class="bw-input mt-2 w-full">
                    </div>
                    <p class="text-sm text-tan">Your friends get an invitation. The book moves to “Reading” on your shelf, and you can set your bookmark once you’re in.</p>
                    <button type="submit" class="btn-primary w-full">Start buddy read</button>
                </div>
            </form>
        </section>
    </div>
</x-app-layout>
