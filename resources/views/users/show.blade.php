<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-tan dark:text-[#c7a491]">Bookworm community</p>
                <h1 class="mt-1 font-serif text-3xl font-semibold text-ink dark:text-[#f4e9e2]">{{ $isOwnProfile ? 'My bookshelf' : 'Reader profile' }}</h1>
            </div>
            <a href="{{ route('books.index') }}" class="focus-ring rounded-sm text-sm font-medium text-burgundy hover:underline dark:text-[#e4b3a5]">
                &larr; Explore books
            </a>
        </div>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="mx-auto max-w-5xl space-y-8 px-4">
            @if (session('status'))
                <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-900 dark:bg-green-950 dark:text-green-200" role="status" aria-live="polite">
                    {{ session('status') }}
                </div>
            @endif

            <section aria-labelledby="reader-profile-heading" class="bookplate relative overflow-hidden rounded-xl">
                <div class="h-1 bg-gradient-to-r from-burgundy-deep via-burgundy to-burgundy-hover"></div>
                <span class="absolute right-8 top-0 z-10 h-12 w-6 bg-gold shadow-sm [clip-path:polygon(0_0,100%_0,100%_100%,50%_78%,0_100%)]" aria-hidden="true"></span>
                <div class="p-5 sm:p-7">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
                        <x-avatar :src="$profile->profile_photo_url" :name="$profile->name" size="lg" />

                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-tan dark:text-[#c7a491]">Bookworm reader</p>
                            <h2 id="reader-profile-heading" class="mt-1 break-words font-serif text-3xl font-semibold leading-tight text-ink dark:text-[#f4e9e2]">{{ $profile->name }}</h2>
                            <p class="mt-1 text-sm text-tan dark:text-[#c1aaa0]">{{ '@' . $profile->username }}</p>

                            @if ($profile->bio)
                                <p class="mt-4 max-w-2xl whitespace-pre-line break-words text-sm leading-7 text-walnut dark:text-[#e3d3ca]">{{ $profile->bio }}</p>
                            @else
                                <p class="mt-4 text-sm italic text-tan dark:text-[#b39d93]">No introduction yet.</p>
                            @endif

                            <p class="mt-4 text-xs text-tan dark:text-[#b39d93]">
                                Reading with Bookworm since {{ $profile->created_at->format('F Y') }}
                            </p>
                        </div>

                        <div class="shrink-0 sm:pt-1">
                            @if ($isOwnProfile)
                                <a href="{{ route('profile.edit') }}"
                                   class="focus-ring inline-flex w-full items-center justify-center rounded-full border border-burgundy/35 px-5 py-2.5 text-sm font-semibold text-burgundy transition hover:bg-rosetint dark:border-burgundy-dark/50 dark:text-[#f0d8c1] dark:hover:bg-[#382c2d] sm:w-auto">
                                    Edit profile and photo
                                </a>
                            @elseif ($isFollowing)
                                <form method="POST" action="{{ route('users.unfollow', $profile->username) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            aria-label="Unfollow {{ $profile->name }}"
                                            class="focus-ring inline-flex w-full items-center justify-center rounded-full border border-burgundy/35 px-5 py-2.5 text-sm font-semibold text-burgundy transition hover:bg-rosetint dark:border-burgundy-dark/50 dark:text-[#f0d8c1] dark:hover:bg-[#382c2d] sm:w-auto">
                                        Following · Unfollow
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('users.follow', $profile->username) }}">
                                    @csrf
                                    <button type="submit"
                                            aria-label="Follow {{ $profile->name }}"
                                            class="focus-ring inline-flex w-full items-center justify-center rounded-full bg-burgundy px-6 py-2.5 text-sm font-semibold text-[#fff8ef] shadow-sm transition hover:bg-burgundy-hover active:bg-burgundy-deep sm:w-auto">
                                        Follow reader
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <dl class="mt-7 grid grid-cols-3 overflow-hidden rounded-lg border border-warmborder bg-parchment/60 dark:border-[#453538] dark:bg-[#241b1c]">
                        <div class="px-2 py-4 text-center sm:px-4">
                            <dt class="font-hand text-xl leading-none text-tan dark:text-[#c1aaa0]">Followers</dt>
                            <dd class="mt-1 font-serif text-3xl font-semibold text-ink dark:text-[#f4e9e2]">{{ $profile->followers_count }}</dd>
                        </div>
                        <div class="border-x border-warmborder px-2 py-4 text-center dark:border-[#453538] sm:px-4">
                            <dt class="font-hand text-xl leading-none text-tan dark:text-[#c1aaa0]">Following</dt>
                            <dd class="mt-1 font-serif text-3xl font-semibold text-ink dark:text-[#f4e9e2]">{{ $profile->following_count }}</dd>
                        </div>
                        <div class="px-2 py-4 text-center sm:px-4">
                            <dt class="font-hand text-xl leading-none text-tan dark:text-[#c1aaa0]">Reviews</dt>
                            <dd class="mt-1 font-serif text-3xl font-semibold text-ink dark:text-[#f4e9e2]">{{ $profile->reviews_count }}</dd>
                        </div>
                    </dl>
                </div>
            </section>

            @if ($isOwnProfile)
                @php($goalPercent = min(100, (int) round($readingStats['books_this_year'] / max(1, $readingStats['yearly_goal']) * 100)))
                <section aria-labelledby="yearly-reading-heading" class="bookplate rounded-xl p-5 sm:p-7">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-tan dark:text-[#c7a491]">A look back at {{ now()->format('Y') }}</p>
                            <h2 id="yearly-reading-heading" class="mt-1 font-serif text-2xl font-semibold text-ink dark:text-[#F4E9E2]">Your reading year</h2>
                        </div>
                        <a href="#bookshelf" class="focus-ring rounded-full border border-burgundy/30 px-4 py-2 text-xs font-semibold text-burgundy hover:bg-rosetint dark:text-burgundy-dark">Open my bookshelf</a>
                    </div>
                    <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-3">
                        <div class="rounded-lg bg-parchment/70 p-4 dark:bg-[#201317]">
                            <p class="text-xs text-tan dark:text-[#c7a491]">Books finished</p>
                            <p class="mt-1 font-serif text-3xl font-semibold text-ink dark:text-[#F4E9E2]">{{ $readingStats['books_this_year'] }}</p>
                        </div>
                        <div class="rounded-lg bg-parchment/70 p-4 dark:bg-[#201317]">
                            <p class="text-xs text-tan dark:text-[#c7a491]">Pages logged</p>
                            <p class="mt-1 font-serif text-3xl font-semibold text-ink dark:text-[#F4E9E2]">{{ number_format($readingStats['pages_read']) }}</p>
                        </div>
                        <div class="rounded-lg bg-parchment/70 p-4 dark:bg-[#201317]">
                            <p class="text-xs text-tan dark:text-[#c7a491]">Currently reading</p>
                            <p class="mt-1 font-serif text-3xl font-semibold text-ink dark:text-[#F4E9E2]">{{ $readingStats['currently_reading'] }}</p>
                        </div>
                    </div>
                    <div class="mt-6">
                        <div class="flex items-center justify-between gap-3 text-sm">
                            <p class="font-semibold text-ink dark:text-[#F4E9E2]">Yearly book goal</p>
                            <p class="text-tan dark:text-[#c7a491]">{{ $readingStats['books_this_year'] }} of {{ $readingStats['yearly_goal'] }}</p>
                        </div>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-parchment dark:bg-[#493536]" role="progressbar" aria-label="Yearly reading goal" aria-valuemin="0" aria-valuemax="{{ $readingStats['yearly_goal'] }}" aria-valuenow="{{ min($readingStats['books_this_year'], $readingStats['yearly_goal']) }}">
                            <span class="block h-full rounded-full bg-burgundy transition-all duration-200" style="width: {{ $goalPercent }}%"></span>
                        </div>
                    </div>
                    @if (count($readingStats['genres']))
                        <p class="mt-5 text-sm text-tan dark:text-[#c7a491]">On your mind lately: <span class="font-semibold text-burgundy dark:text-burgundy-dark">{{ implode(' · ', $readingStats['genres']) }}</span></p>
                    @else
                        <p class="mt-5 text-sm text-tan dark:text-[#c7a491]">Add a few favorite genres to personalize your reading nook. <a href="{{ route('onboarding.show') }}" class="font-semibold text-burgundy underline dark:text-burgundy-dark">Set preferences</a></p>
                    @endif
                    @if (count($readingStats['genres']))
                        <a href="{{ route('onboarding.show') }}" class="mt-3 inline-flex rounded-sm text-sm font-semibold text-burgundy underline underline-offset-2 dark:text-burgundy-dark">Update reading preferences</a>
                    @endif
                    <div class="mt-6 border-t border-warmborder pt-5 dark:border-[#4B3035]">
                        <p class="text-xs font-semibold uppercase tracking-[0.16em] text-tan dark:text-[#c7a491]">Books marked read by month</p>
                        <div class="mt-4 grid grid-cols-6 items-end gap-2 sm:grid-cols-12" aria-label="Books read each month">
                            @foreach ($readingStats['monthly_reads'] as $monthIndex => $count)
                                @php($barHeight = $count > 0 ? max(12, min(100, $count * 16)) : 3)
                                <div class="flex flex-col items-center gap-2" aria-label="{{ $count }} {{ Str::plural('book', $count) }} in {{ \Carbon\Carbon::create()->month($monthIndex + 1)->format('F') }}">
                                    <span class="w-full rounded-t-sm bg-burgundy/80 dark:bg-burgundy-dark/80" style="height: {{ $barHeight }}px"></span>
                                    <span class="text-[10px] text-tan dark:text-[#c7a491]">{{ \Carbon\Carbon::create()->month($monthIndex + 1)->format('M') }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </section>
            @endif

            <x-section-divider class="my-1" />
            <section id="bookshelf" aria-labelledby="reviews-heading">
                <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-tan dark:text-[#c7a491]">{{ $isOwnProfile ? 'Bookshelf · your reading life, all on one shelf' : 'Reader collection' }}</p>
                        <h2 id="reviews-heading" class="mt-1 font-serif text-2xl font-semibold text-ink dark:text-[#f4e9e2]">
                            {{ $isOwnProfile ? 'My bookshelf' : "Bookshelf by " . $profile->name }}
                        </h2>
                    </div>
                    <span class="text-sm text-[#8a7065] dark:text-[#c1aaa0]">
                        {{ $bookshelf->total() }} {{ Str::plural('book', $bookshelf->total()) }}
                    </span>
                </div>

                @if ($isOwnProfile)
                    <div class="bookplate mb-5 rounded-xl p-4 sm:p-5">
                        <form method="GET" action="{{ route('users.show', $profile->username) }}" class="flex flex-col gap-3 sm:flex-row">
                            <label for="shelf-search" class="sr-only">Search your bookshelf</label>
                            <x-text-input id="shelf-search" name="q" type="search" class="min-w-0 flex-1 px-4 py-3" placeholder="Find a book on your shelf" :value="$shelfQuery" />
                            <label for="shelf-sort" class="sr-only">Sort your bookshelf</label>
                            <select id="shelf-sort" name="sort" class="min-h-11 rounded-lg border-warmborder bg-paper px-3 text-sm text-ink focus:border-burgundy focus:ring-burgundy/20 dark:border-[#4B3035] dark:bg-[#201317] dark:text-[#F4E9E2]">
                                <option value="recent" @selected($shelfSort === 'recent')>Recently updated</option>
                                <option value="added" @selected($shelfSort === 'added')>Recently added</option>
                                <option value="title" @selected($shelfSort === 'title')>Title A–Z</option>
                            </select>
                            <button type="submit" class="focus-ring min-h-11 rounded-full bg-burgundy px-5 py-2.5 text-sm font-semibold text-[#FFF8EF] transition hover:bg-burgundy-hover">Find book</button>
                        </form>
                        <nav class="mt-4 flex flex-wrap gap-2" aria-label="Filter your bookshelf">
                            <a href="{{ route('users.show', ['user' => $profile->username, 'sort' => $shelfSort, 'q' => $shelfQuery]) }}" @if ($shelfStatus === '') aria-current="page" @endif class="{{ $shelfStatus === '' ? 'bg-burgundy text-white' : 'border border-warmborder bg-paper text-walnut dark:border-[#4B3035] dark:bg-raised dark:text-[#d4c2b9]' }} focus-ring rounded-full px-4 py-2 text-sm font-semibold transition hover:border-burgundy">All <span class="ml-1 opacity-75">{{ $shelfStatusCounts->get('all', 0) }}</span></a>
                            @foreach ($shelfStatuses as $key => $label)
                                <a href="{{ route('users.show', ['user' => $profile->username, 'status' => $key, 'sort' => $shelfSort, 'q' => $shelfQuery]) }}" @if ($shelfStatus === $key) aria-current="page" @endif class="{{ $shelfStatus === $key ? 'bg-burgundy text-white' : 'border border-warmborder bg-paper text-walnut dark:border-[#4B3035] dark:bg-raised dark:text-[#d4c2b9]' }} focus-ring rounded-full px-4 py-2 text-sm font-semibold transition hover:border-burgundy">{{ $label }} <span class="ml-1 opacity-75">{{ $shelfStatusCounts->get($key, 0) }}</span></a>
                            @endforeach
                        </nav>
                    </div>
                @endif

                <div class="rounded-3xl border border-[#eadfd0] bg-gradient-to-br from-[#fffaf1] via-[#fffdf9] to-[#f8eee3] p-4 shadow-[0_18px_50px_rgba(91,58,43,0.08)] sm:p-6">
                    <div class="mb-5 flex flex-wrap items-center justify-between gap-3 rounded-2xl border border-[#f0e2d2] bg-white/75 px-4 py-3">
                        <div>
                            <p class="font-serif text-lg font-semibold text-[#49352f]">A shelf that feels like yours</p>
                            <p class="mt-0.5 text-sm text-[#8b7569]">Tap a cover to revisit its story. Open a card to update your reading list.</p>
                        </div>
                        <span class="rounded-full bg-[#f7eadb] px-3 py-1.5 text-xs font-semibold text-[#825b46]">{{ $bookshelf->total() }} {{ Str::plural('book', $bookshelf->total()) }}</span>
                    </div>

                    @forelse ($bookshelf->getCollection()->chunk(5) as $shelf)
                        <div class="relative mb-7 overflow-hidden rounded-xl border-[10px] border-[#a96c42] bg-[linear-gradient(90deg,rgba(92,50,27,0.16),transparent_16%,rgba(255,232,193,0.2)_50%,transparent_84%,rgba(92,50,27,0.16)),repeating-linear-gradient(2deg,#b77d50_0px,#c58b5a_5px,#b77d50_11px,#d09a68_18px)] shadow-[0_12px_26px_rgba(76,45,29,0.2),inset_0_0_0_2px_rgba(255,228,190,0.38)] last:mb-0">
                            <div class="h-3 border-b border-[#9a6039] bg-[linear-gradient(180deg,#d09a68,#b77d50)]" aria-hidden="true"></div>
                            <div class="mx-2 border-x-[10px] border-[#b77d50] bg-[#d6b18a] shadow-[inset_8px_0_12px_rgba(92,50,27,0.12),inset_-8px_0_12px_rgba(92,50,27,0.12)]">
                            <div role="region" aria-label="Bookshelf row" tabindex="0" class="relative flex min-h-64 items-end gap-4 overflow-x-auto bg-[linear-gradient(90deg,rgba(119,74,43,0.08),transparent_18%,rgba(255,244,221,0.2)_50%,transparent_82%,rgba(119,74,43,0.08)),repeating-linear-gradient(0deg,#d9b891_0px,#e4c7a2_8px,#d4ae84_18px,#e2c29a_29px)] px-4 pt-8 shadow-[inset_0_10px_18px_rgba(102,66,40,0.13)] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#8b3e4b] sm:justify-center sm:gap-5 sm:px-7">
                                @foreach ($shelf as $item)
                                    <article class="group/book flex w-28 flex-none flex-col items-center sm:w-32">
                                        <a href="{{ route('books.show', $item->book) }}"
                                           aria-label="{{ $item->book->title }} by {{ $item->book->author_name ?? 'Unknown author' }}, {{ $shelfStatuses[$item->status] ?? 'on shelf' }}"
                                           title="{{ $item->book->title }} · {{ $shelfStatuses[$item->status] ?? 'On shelf' }}"
                                           class="relative block aspect-[2/3] w-full overflow-hidden rounded-t-[3px] bg-gradient-to-br from-[#f3dfc7] via-[#f9eee0] to-[#e8d1bc] shadow-[3px_1px_5px_rgba(73,53,47,0.24),inset_4px_0_6px_rgba(255,255,255,0.35)] transition duration-200 group-hover/book:-translate-y-1 group-hover/book:shadow-[4px_5px_12px_rgba(73,53,47,0.25)] focus-visible:z-10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#9a5d48]">
                                            @if ($item->book->cover_url)
                                                <img src="{{ $item->book->cover_url }}" alt="Cover of {{ $item->book->title }}" loading="lazy" class="h-full w-full object-cover">
                                            @else
                                                <span class="flex h-full flex-col items-center justify-center border-y-[5px] border-[#bd9367] px-3 text-center text-[#795544]">
                                                    <svg class="mb-3 h-8 w-8 opacity-60" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 5.75A2.75 2.75 0 0 1 7.25 3H20v17H7.25A2.75 2.75 0 0 0 4.5 22V5.75Zm0 0V22M8 7h8M8 10h8" />
                                                    </svg>
                                                    <span class="line-clamp-4 font-serif text-sm font-semibold leading-tight">{{ $item->book->title }}</span>
                                                    @if ($item->book->author_name)
                                                        <span class="mt-2 line-clamp-2 text-[10px]">{{ $item->book->author_name }}</span>
                                                    @endif
                                                </span>
                                            @endif
                                        </a>
                                        <span class="mt-2 max-w-full truncate text-center text-[11px] font-medium text-[#765c4d]">{{ $shelfStatuses[$item->status] ?? 'Read' }}</span>
                                    </article>
                                @endforeach
                            </div>
                            <div class="relative z-10 h-7 border-y border-[#9e663e] bg-[linear-gradient(180deg,#f1d7af_0%,#dbb17f_24%,#c18a59_78%,#a96f45_100%)] shadow-[0_8px_12px_rgba(76,45,29,0.27)]" aria-hidden="true">
                                <span class="absolute inset-x-0 top-0 h-px bg-[#fff2d9]"></span>
                                <span class="absolute inset-x-0 top-1 h-px bg-[#f7dfb9]/70"></span>
                                <span class="absolute inset-x-0 bottom-1 h-px bg-[#9c704d]/40"></span>
                            </div>
                            </div>
                            <div class="mx-2 border-x-[10px] border-[#b77d50] bg-[#d6b18a]">
                            <div class="grid gap-px border-t-[5px] border-[#b77d50] bg-[#c89566] sm:grid-cols-2 lg:grid-cols-5">
                                @foreach ($shelf as $item)
                                    <article class="min-w-0 bg-white/90 p-3 sm:p-4">
                                        <a href="{{ route('books.show', $item->book) }}" class="line-clamp-2 font-serif text-sm font-semibold leading-snug text-[#49352f] hover:text-[#8b3e4b] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#9a5d48]">
                                            {{ $item->book->title }}
                                        </a>
                                        @if ($item->book->author_name)
                                            <p class="mt-1 line-clamp-1 text-xs text-[#8b7569]">{{ $item->book->author_name }}</p>
                                        @endif

                                        @if ($item->status === 'reading' && $item->total_pages)
                                            @php($progress = min(100, (int) round(($item->current_page ?? 0) / $item->total_pages * 100)))
                                            <p class="mt-2 text-xs text-[#80695d]">Page {{ $item->current_page ?? 0 }} of {{ $item->total_pages }} · {{ $progress }}%</p>
                                            <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-[#f1e7dc]">
                                                <span class="block h-full rounded-full bg-gradient-to-r from-[#d5a469] to-[#b56d68]" style="width: {{ $progress }}%"></span>
                                            </div>
                                        @elseif ($item->rating)
                                            <div class="mt-2"><x-star-display :rating="$item->rating" /></div>
                                        @endif

                                        @if ($isOwnProfile)
                                            <details class="group/menu mt-3 border-t border-[#f0e7de] pt-2">
                                                <summary class="flex min-h-9 cursor-pointer list-none items-center justify-between gap-2 rounded-lg px-2 text-xs font-semibold text-[#805c4b] transition hover:bg-[#fbf5ee] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#9a5d48] [&::-webkit-details-marker]:hidden">
                                                    <span>{{ $item->status === 'reading' ? 'Reading options' : 'Update my list' }}</span>
                                                    <svg class="h-4 w-4 transition group-open/menu:rotate-180" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">
                                                        <path stroke-linecap="round" stroke-linejoin="round" d="m5 7.5 5 5 5-5" />
                                                    </svg>
                                                </summary>
                                                <div class="mt-2 rounded-lg bg-[#fcf8f2] p-2">
                                                    <p class="mb-1.5 text-[10px] font-semibold uppercase tracking-[0.12em] text-[#9a8172]">Move to</p>
                                                    <div class="grid gap-1">
                                                        @foreach ($shelfStatuses as $key => $label)
                                                            <form method="POST" action="{{ route('books.shelf.update', $item->book) }}">
                                                                @csrf
                                                                <button type="submit" name="status" value="{{ $key }}" @if ($item->status === $key) aria-current="true" @endif class="w-full rounded-md px-2 py-1.5 text-left text-xs font-medium transition {{ $item->status === $key ? 'bg-[#efe1d2] text-[#694b3d]' : 'text-[#80695d] hover:bg-white hover:text-[#49352f]' }}">
                                                                    {{ $item->status === $key ? '✓ ' : '' }}{{ $label }}
                                                                </button>
                                                            </form>
                                                        @endforeach
                                                    </div>
                                                    @if ($item->status === 'reading')
                                                        <form method="POST" action="{{ route('books.shelf.update', $item->book) }}" class="mt-2 border-t border-[#eee3d7] pt-2">
                                                            @csrf
                                                            <input type="hidden" name="status" value="reading">
                                                            <label class="block text-[11px] text-[#80695d]">Current page
                                                                <input name="current_page" type="number" min="0" max="1000000" value="{{ $item->current_page }}" class="mt-1 min-h-8 w-full rounded-md border-[#e8d9ca] bg-white px-2 text-xs text-[#49352f] focus:border-[#a86b5b] focus:ring-[#a86b5b]/20">
                                                            </label>
                                                            <label class="mt-2 block text-[11px] text-[#80695d]">Total pages
                                                                <input name="total_pages" type="number" min="1" max="1000000" value="{{ $item->total_pages }}" class="mt-1 min-h-8 w-full rounded-md border-[#e8d9ca] bg-white px-2 text-xs text-[#49352f] focus:border-[#a86b5b] focus:ring-[#a86b5b]/20">
                                                            </label>
                                                            <button type="submit" class="mt-2 rounded-full bg-[#8b3e4b] px-3 py-1.5 text-[11px] font-semibold text-white transition hover:bg-[#71303c]">Save progress</button>
                                                        </form>
                                                    @endif
                                                    @if ($item->book->bookPdfs->isNotEmpty())
                                                        <a href="{{ route('books.pdfs.reader', [$item->book, $item->book->bookPdfs->first()]) }}" class="mt-2 inline-flex w-full items-center justify-center rounded-full border border-[#e3d2c1] bg-white px-2 py-1.5 text-xs font-semibold text-[#80513f] transition hover:border-[#cda995] hover:bg-[#fffaf4]">Continue reading</a>
                                                    @endif
                                                </div>
                                            </details>
                                        @endif

                                        @if ($item->review_body)
                                            <p class="mt-2 line-clamp-2 border-t border-[#f0e7de] pt-2 text-xs leading-5 text-[#80695d]">{{ $item->review_body }}</p>
                                        @endif
                                    </article>
                                @endforeach
                            </div>
                            </div>
                            <div class="h-3 border-t border-[#9a6039] bg-[linear-gradient(180deg,#bd8555,#a96c42)]" aria-hidden="true"></div>
                        </div>
                    @empty
                        <div class="rounded-2xl border border-dashed border-[#e6d5c4] bg-white/70 px-5 py-12 text-center">
                            <x-logo variant="icon" tone="dark" class="mx-auto h-14 w-14 rounded-full bg-[#f5e8da] p-2" />
                            <p class="mt-3 font-serif text-2xl font-semibold text-[#594238]">{{ $shelfQuery !== '' || $shelfStatus !== '' ? 'No books match this shelf view' : 'A little room for your first story' }}</p>
                            <p class="mt-2 text-sm text-[#8b7569]">{{ $shelfQuery !== '' || $shelfStatus !== '' ? 'Try another search or choose a different shelf.' : 'Add a book to your shelf and it will find its place here.' }}</p>
                            @if ($isOwnProfile)
                                <a href="{{ route('books.index') }}" class="mt-5 inline-flex rounded-full bg-[#8b3e4b] px-5 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-[#71303c]">
                                    Find a book for my shelf
                                </a>
                            @endif
                            @endforelse
                        </div>
                </div>

                @if ($bookshelf->hasPages())
                    <div class="mt-6">{{ $bookshelf->withQueryString()->links() }}</div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
