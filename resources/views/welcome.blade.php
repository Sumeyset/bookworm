<x-app-layout>
    <x-slot name="header">
        <x-page-header :eyebrow="now()->format('l, F j')" :title="'Welcome, ' . Auth::user()->name . '!'" subtitle="Find your next favorite book, and share the stories you love.">
            <x-slot name="actions">
                <a href="{{ route('books.index') }}" class="btn-primary">Find a book to keep <span aria-hidden="true">&rarr;</span></a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div>
        <div class="page-section space-y-8">
            @if (! Auth::user()->onboarding_completed_at)
                <aside class="bw-card flex flex-col justify-between gap-4 p-5 sm:flex-row sm:items-center">
                    <div>
                        <p class="font-serif text-xl font-semibold text-ink dark:text-[#F4E9E2]">Make this reading nook yours.</p>
                        <p class="mt-1 text-sm text-walnut dark:text-[#d4c2b9]">Choose a few favorite genres and set a gentle yearly goal.</p>
                    </div>
                    <a href="{{ route('onboarding.show') }}" class="btn-primary shrink-0">Set my preferences</a>
                </aside>
            @endif

            <div class="grid gap-6 lg:grid-cols-[1.4fr_1fr]">
                <article class="bw-card relative overflow-hidden px-6 py-10 sm:px-10" aria-labelledby="daily-quote-title">
                    <span class="pointer-events-none absolute -right-6 -top-10 font-serif text-[12rem] leading-none text-burgundy/5 dark:text-white/5" aria-hidden="true">“</span>
                    <div class="relative mx-auto max-w-3xl">
                        <p class="eyebrow">A little thought for today</p>
                        <h2 id="daily-quote-title" class="sr-only">A little thought for today</h2>
                        @if ($quote)
                            <blockquote class="mt-5 font-serif text-2xl italic leading-relaxed text-ink dark:text-[#f4e9e2] sm:text-3xl">“{{ $quote['text'] }}”</blockquote>
                            <p class="mt-5 font-hand text-3xl text-burgundy dark:text-burgundy-dark">— {{ $quote['author'] }}</p>
                        @elseif ($quoteError)
                            <p class="mt-5 font-serif text-2xl italic leading-relaxed text-walnut dark:text-[#d4c2b9]">Today’s quote couldn’t be loaded just now.</p>
                            <p class="mt-2 text-sm text-tan">In the meantime: a good book is always a good idea.</p>
                        @endif
                        <p class="mt-8 border-t border-warmborder pt-4 text-xs text-tan dark:border-[#4B3035] dark:text-[#c7a491]">
                            Inspirational quotes provided by
                            <a href="https://zenquotes.io/" target="_blank" rel="noopener noreferrer" class="font-semibold text-burgundy underline decoration-gold/70 underline-offset-2 hover:text-burgundy-hover dark:text-burgundy-dark">ZenQuotes API</a>
                        </p>
                    </div>
                </article>

                <a href="{{ route('users.show', Auth::user()->username) }}" class="group relative flex flex-col justify-between overflow-hidden rounded-3xl bg-gradient-to-br from-[#4e6b39] to-[#2f4426] p-7 text-[#FFF8EF] shadow-bookplate transition hover:-translate-y-1">
                    <x-logo variant="icon" tone="light" size="fill" class="absolute -bottom-4 -right-4 h-36 w-36 opacity-90 transition group-hover:rotate-6" />
                    <div class="relative">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#EAF2D8]/80">Your living library</p>
                        <p class="font-display mt-3 text-6xl font-bold">{{ $shelfCount }}</p>
                        <p class="font-hand text-2xl text-[#EAF2D8]">{{ Str::plural('book', $shelfCount) }} on your tree</p>
                    </div>
                    <span class="relative mt-8 inline-flex w-fit items-center gap-2 rounded-full bg-white/15 px-4 py-2 text-sm font-semibold backdrop-blur">Visit my tree <span aria-hidden="true">&rarr;</span></span>
                </a>
            </div>

            {{-- Streak + year recap --}}
            <div class="grid gap-6 md:grid-cols-2">
                <div class="relative flex items-center gap-5 overflow-hidden rounded-3xl bg-gradient-to-br from-[#B8893B] via-[#C9733F] to-[#8B2A3D] p-6 text-[#FFF8EF] shadow-bookplate">
                    <span class="text-6xl drop-shadow-lg {{ $streak ? 'animate-[bw-flicker_1.8s_ease-in-out_infinite]' : 'grayscale' }}" aria-hidden="true">🔥</span>
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#FFF1D6]/85">Reading streak</p>
                        <p class="font-display text-5xl font-bold leading-none">{{ $streak }} {{ Str::plural('day', $streak) }}</p>
                        <p class="mt-1 text-sm text-[#FFF1D6]">{{ $readToday ? 'You read today — the streak is safe. 🌟' : ($streak ? 'Move your bookmark today to keep it going.' : 'Move a bookmark today to start one.') }}</p>
                    </div>
                </div>
                <a href="{{ route('year.show', Auth::user()->username) }}" class="group relative flex items-center justify-between gap-4 overflow-hidden rounded-3xl bg-wine-header p-6 text-[#FFF8EF] shadow-bookplate transition hover:-translate-y-0.5">
                    <span class="pointer-events-none absolute -right-8 -top-10 h-32 w-32 rounded-full bg-gold/25 blur-2xl" aria-hidden="true"></span>
                    <div class="relative">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#F2C38B]">Your recap</p>
                        <p class="font-display text-5xl font-bold leading-none">{{ now()->year }} in books</p>
                        <p class="mt-1 text-sm text-[#F1D9C4]">Books, pages, streaks and favourites — ready to share.</p>
                    </div>
                    <span class="relative text-3xl transition group-hover:translate-x-1" aria-hidden="true">&rarr;</span>
                </a>
            </div>

            <section aria-labelledby="continue-heading">
                <div class="mb-4 flex items-end justify-between gap-3">
                    <div>
                        <p class="eyebrow">Pick up where you left off</p>
                        <h2 id="continue-heading" class="font-display mt-1 text-3xl text-ink dark:text-[#F4E9E2] font-bold">Continue reading</h2>
                    </div>
                </div>
                @if ($reading->isEmpty())
                    <x-empty-state title="No bookmark yet" message="Mark a book as “Reading” on your tree and it will wait for you here." />
                @else
                    <ul class="grid gap-4 sm:grid-cols-2">
                        @foreach ($reading as $item)
                            @php($percent = $item->progressPercent())
                            <li>
                                <a href="{{ route('books.show', $item->book) }}" class="bw-card group flex gap-4 p-4 transition hover:-translate-y-0.5">
                                    @if ($item->book->cover_url)
                                        <img src="{{ $item->book->cover_src }}" data-cover-fallback="{{ Str::limit($item->book->title, 40) }}" alt="" loading="lazy" decoding="async" width="64" height="96" class="aspect-[2/3] w-16 shrink-0 rounded-md object-cover shadow-md">
                                    @else
                                        <span class="flex aspect-[2/3] w-16 shrink-0 items-center justify-center rounded-md bg-gradient-to-br from-burgundy to-burgundy-deep p-1 text-center font-serif text-[10px] text-[#FFF8EF]">{{ Str::limit($item->book->title, 28) }}</span>
                                    @endif
                                    <div class="min-w-0 flex-1">
                                        <p class="line-clamp-2 font-serif text-lg font-semibold leading-6 text-ink group-hover:text-burgundy dark:text-[#F4E9E2]">{{ $item->book->title }}</p>
                                        <p class="truncate text-xs text-tan">{{ $item->book->author_name ?? 'Unknown author' }}</p>
                                        <p class="mt-3 text-xs font-semibold text-walnut dark:text-[#d4c2b9]">
                                            {{ $item->current_page ? 'Bookmark on page ' . $item->current_page . ($item->total_pages ? ' of ' . $item->total_pages : '') : 'Just started' }}
                                        </p>
                                        <div class="mt-1.5 h-1.5 overflow-hidden rounded-full bg-parchment dark:bg-white/10"><span class="block h-full rounded-full bg-gradient-to-r from-gold to-[#f3cf7a]" style="width: {{ $percent ?? 4 }}%"></span></div>
                                    </div>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </section>
            @if (! empty($recommendations))
                @include('books.partials.recommendations')
            @endif
        </div>
    </div>
</x-app-layout>
