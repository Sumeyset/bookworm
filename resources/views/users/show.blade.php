<x-app-layout>
    <x-slot name="title">{{ $isOwnProfile ? 'My book tree' : $profile->name . '’s book tree' }} · Bookworm</x-slot>

    <div class="page-section space-y-10">
        {{-- Reader hero --}}
        <section aria-labelledby="reader-profile-heading" class="bw-card relative overflow-hidden">
            <div class="pointer-events-none absolute inset-x-0 top-0 h-28 bg-wine-header opacity-95" aria-hidden="true"></div>
            <div class="pointer-events-none absolute right-6 top-4 font-hand text-5xl text-[#F2C38B]/25" aria-hidden="true">❦</div>
            <div class="relative flex flex-col gap-5 px-5 pb-6 pt-16 sm:flex-row sm:items-end sm:px-8 sm:pt-32">
                <x-avatar :src="$profile->profile_photo_url" :name="$profile->name" size="lg" class="ring-4 ring-paper dark:ring-raised sm:-mt-16 sm:self-start" />
                <div class="min-w-0 flex-1">
                    <p class="eyebrow">{{ $isOwnProfile ? 'Your reading life' : 'Bookworm reader' }}</p>
                    <h1 id="reader-profile-heading" class="font-display mt-1 break-words text-4xl leading-tight text-ink dark:text-[#f4e9e2] sm:text-5xl font-bold">{{ $profile->name }}</h1>
                    <p class="mt-1 text-sm text-tan dark:text-[#c1aaa0]">{{ '@' . $profile->username }} · reading since {{ $profile->created_at->format('F Y') }}</p>
                </div>
                <div class="shrink-0">
                    @if ($isOwnProfile)
                        <a href="{{ route('profile.edit') }}" class="btn-ghost">Edit profile and photo</a>
                    @elseif ($isFollowing)
                        <form method="POST" action="{{ route('users.unfollow', $profile->username) }}">@csrf @method('DELETE')<button type="submit" class="btn-ghost">Following · Unfollow</button></form>
                    @else
                        <form method="POST" action="{{ route('users.follow', $profile->username) }}">@csrf<button type="submit" class="btn-primary">Follow reader</button></form>
                    @endif
                </div>
            </div>
            <div class="relative grid gap-5 border-t border-warmborder px-5 py-5 dark:border-[#4B3035] sm:grid-cols-[1fr_auto] sm:items-center sm:px-8">
                <p class="whitespace-pre-line break-words font-serif text-lg italic leading-8 text-walnut dark:text-[#e3d3ca]">{{ $profile->bio ?: 'No introduction yet.' }}</p>
                <dl class="grid grid-cols-3 gap-2 text-center">
                    @foreach (['Followers' => $profile->followers_count, 'Following' => $profile->following_count, 'Reviews' => $profile->reviews_count] as $label => $value)
                        <div class="rounded-2xl bg-parchment/70 px-4 py-2.5 dark:bg-white/5">
                            <dd class="font-display text-3xl text-ink dark:text-[#f4e9e2] font-bold">{{ $value }}</dd>
                            <dt class="font-hand text-lg leading-none text-tan">{{ $label }}</dt>
                        </div>
                    @endforeach
                </dl>
            </div>
        </section>

        {{-- The living tree --}}
        <section id="bookshelf" aria-labelledby="bookshelf-heading" class="scroll-mt-24">
            <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="eyebrow">{{ $isOwnProfile ? 'Bookshelf · your reading life, all on one shelf' : 'Reader collection' }}</p>
                    <h2 id="bookshelf-heading" class="font-display mt-1 text-4xl text-ink dark:text-[#f4e9e2] font-bold">{{ $isOwnProfile ? 'My bookshelf' : 'Bookshelf by ' . $profile->name }}</h2>
                </div>
                @if ($isOwnProfile)
                    <a href="{{ route('books.index') }}" class="btn-primary">+ Add a book</a>
                @endif
            </div>
            @include('profile.partials.tree-bookshelf')
        </section>

        @if ($isOwnProfile)
            @php $goalPercent = min(100, (int) round($readingStats['books_this_year'] / max(1, $readingStats['yearly_goal']) * 100)); @endphp
            <section aria-labelledby="yearly-reading-heading" class="bw-card p-5 sm:p-8">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="eyebrow">A look back at {{ now()->format('Y') }}</p>
                        <h2 id="yearly-reading-heading" class="font-display mt-1 text-3xl text-ink dark:text-[#f4e9e2] font-bold">Your reading year</h2>
                    </div>
                    @if (count($readingStats['genres']))
                        <a href="{{ route('onboarding.show') }}" class="btn-ghost text-xs">Update reading preferences</a>
                    @else
                        <a href="{{ route('onboarding.show') }}" class="btn-ghost text-xs">Set preferences</a>
                    @endif
                </div>
                <div class="mt-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
                    @foreach ([['Books finished', $readingStats['books_this_year']], ['Currently reading', $readingStats['currently_reading']], ['Pages read', number_format($readingStats['pages_read'])]] as [$label, $value])
                        <div class="rounded-2xl bg-parchment/70 p-4 dark:bg-white/5"><p class="text-xs font-semibold text-tan">{{ $label }}</p><p class="font-display mt-1 text-4xl text-ink dark:text-[#f4e9e2] font-bold">{{ $value }}</p></div>
                    @endforeach
                    <div class="rounded-2xl bg-gradient-to-br from-burgundy to-burgundy-deep p-4 text-[#FFF8EF]">
                        <p class="text-xs font-semibold text-[#F1D9C4]">Yearly goal</p>
                        <p class="font-display mt-1 text-4xl font-bold">{{ $readingStats['books_this_year'] }} / {{ $readingStats['yearly_goal'] }}</p>
                        <div class="mt-2 h-2 overflow-hidden rounded-full bg-white/20" role="progressbar" aria-label="Yearly reading goal" aria-valuemin="0" aria-valuemax="{{ $readingStats['yearly_goal'] }}" aria-valuenow="{{ min($readingStats['books_this_year'], $readingStats['yearly_goal']) }}"><span class="block h-full rounded-full bg-[#F2C38B]" style="width: {{ $goalPercent }}%"></span></div>
                    </div>
                </div>
                @if (count($readingStats['genres']))
                    <p class="mt-5 text-sm text-tan">On your mind lately: <span class="font-semibold text-burgundy dark:text-[#E9A2AE]">{{ implode(' · ', $readingStats['genres']) }}</span></p>
                @endif
                <div class="mt-6 border-t border-warmborder pt-5 dark:border-[#4B3035]">
                    <p class="eyebrow">Books marked read by month</p>
                    <div class="mt-4 grid h-32 grid-cols-12 items-end gap-1.5 sm:gap-2" aria-label="Books read each month">
                        @foreach ($readingStats['monthly_reads'] as $monthIndex => $count)
                            @php $barHeight = $count > 0 ? max(14, min(100, $count * 18)) : 4; @endphp
                            <div class="flex h-full flex-col items-center justify-end gap-1.5" aria-label="{{ $count }} {{ Str::plural('book', $count) }} in {{ \Carbon\Carbon::create()->month($monthIndex + 1)->format('F') }}">
                                @if ($count)<span class="text-[10px] font-semibold text-burgundy dark:text-[#E9A2AE]">{{ $count }}</span>@endif
                                <span class="w-full rounded-t-lg {{ $count ? 'bg-gradient-to-t from-burgundy to-burgundy-hover dark:from-burgundy-dark dark:to-[#c25670]' : 'bg-warmborder dark:bg-white/10' }}" style="height: {{ $barHeight }}%"></span>
                                <span class="text-[10px] text-tan dark:text-[#c7a491]">{{ \Carbon\Carbon::create()->month($monthIndex + 1)->format('M') }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        {{-- List view with search & filters --}}
        <section id="shelf-list" aria-labelledby="shelf-list-heading" class="scroll-mt-24">
            <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <p class="eyebrow">Every book, at a glance</p>
                    <h2 id="shelf-list-heading" class="font-display mt-1 text-3xl text-ink dark:text-[#f4e9e2] font-bold">Shelf list</h2>
                </div>
                <span class="text-sm text-tan">{{ $bookshelf->total() }} {{ Str::plural('book', $bookshelf->total()) }}</span>
            </div>

            @if ($isOwnProfile)
                <div class="bw-card mb-5 p-4 sm:p-5">
                    <form method="GET" action="{{ route('users.show', $profile->username) }}#shelf-list" class="flex flex-col gap-3 sm:flex-row">
                        <label for="shelf-search" class="sr-only">Search your bookshelf</label>
                        <input id="shelf-search" name="q" type="search" class="bw-input min-w-0 flex-1" placeholder="Find a book on your shelf" value="{{ $shelfQuery }}">
                        <input type="hidden" name="status" value="{{ $shelfStatus }}">
                        <label for="shelf-sort" class="sr-only">Sort your bookshelf</label>
                        <select id="shelf-sort" name="sort" class="bw-input text-sm">
                            <option value="recent" @selected($shelfSort === 'recent')>Recently updated</option>
                            <option value="added" @selected($shelfSort === 'added')>Recently added</option>
                            <option value="title" @selected($shelfSort === 'title')>Title A–Z</option>
                        </select>
                        <button type="submit" class="btn-primary">Find book</button>
                    </form>
                    <nav class="mt-4 flex flex-wrap gap-2" aria-label="Filter your bookshelf">
                        @foreach (['' => 'All'] + $shelfStatuses as $key => $label)
                            <a href="{{ route('users.show', array_filter(['user' => $profile->username, 'status' => $key, 'sort' => $shelfSort, 'q' => $shelfQuery])) }}#shelf-list"
                               @if ($shelfStatus === $key) aria-current="page" @endif
                               class="{{ $shelfStatus === $key ? 'border-burgundy bg-burgundy text-[#FFF8EF] dark:border-burgundy-dark dark:bg-burgundy-dark' : 'border-warmborder text-walnut hover:border-burgundy/40 dark:border-[#4B3035] dark:text-[#e3d3ca]' }} focus-ring rounded-full border px-4 py-2 text-sm font-semibold transition">
                                {{ $label }} <span class="opacity-70">{{ $shelfStatusCounts->get($key === '' ? 'all' : $key, 0) }}</span>
                            </a>
                        @endforeach
                    </nav>
                </div>
            @endif

            @if ($bookshelf->isEmpty())
                <x-empty-state :title="$isOwnProfile ? 'Nothing here yet' : 'No shared reviews yet'" :message="$isOwnProfile ? 'Try a different search or filter, or add a new book.' : 'Books this reader reviews will appear here.'" />
            @else
                <ul class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-4">
                    @foreach ($bookshelf as $item)
                        @php($book = $item->book)
                        <li class="bw-card group flex gap-3 p-3 transition hover:-translate-y-0.5">
                            <a href="{{ route('books.show', $book) }}" class="shrink-0">
                                @if ($book->cover_url)
                                    <img src="{{ $book->cover_src }}" data-cover-fallback="{{ Str::limit($book->title, 40) }}" alt="Cover of {{ $book->title }}" loading="lazy" decoding="async" width="64" height="96" class="aspect-[2/3] w-16 rounded-md object-cover shadow-md">
                                @else
                                    <span class="flex aspect-[2/3] w-16 items-center justify-center rounded-md bg-gradient-to-br from-burgundy to-burgundy-deep p-1 text-center font-serif text-[10px] text-[#FFF8EF]">{{ Str::limit($book->title, 30) }}</span>
                                @endif
                            </a>
                            <div class="min-w-0">
                                <a href="{{ route('books.show', $book) }}" class="focus-ring line-clamp-2 font-serif text-base font-semibold leading-5 text-ink hover:text-burgundy dark:text-[#f4e9e2]">{{ $book->title }}</a>
                                <p class="mt-0.5 truncate text-xs text-tan">{{ $book->author_name ?? 'Unknown author' }}</p>
                                <p class="mt-2 inline-flex rounded-full bg-parchment px-2 py-0.5 text-[11px] font-semibold text-walnut dark:bg-white/10 dark:text-[#e3d3ca]">{{ $shelfStatuses[$item->status] ?? 'On shelf' }}</p>
                                @if ($item->rating)
                                    <div class="mt-1.5"><x-star-display :rating="$item->rating" /></div>
                                @endif
                                @if ($item->review_body)
                                    <p class="mt-1 line-clamp-2 font-serif text-xs italic text-walnut dark:text-[#d4c2b9]">{{ $item->review_body }}</p>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
                @if ($bookshelf->hasPages())<div class="mt-6">{{ $bookshelf->withQueryString()->fragment('shelf-list')->links() }}</div>@endif
            @endif
        </section>
    </div>
</x-app-layout>
