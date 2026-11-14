{{-- Site footer: wine-dark panel with brand, quote, links and a back-to-top button --}}
<footer {{ $attributes->class(['relative mt-20 px-3 pb-3 sm:px-5 sm:pb-5']) }}>
    <div class="relative mx-auto max-w-7xl overflow-hidden rounded-[2rem] bg-[linear-gradient(135deg,#2a0d15_0%,#4A1220_45%,#6D1A2B_100%)] text-[#F4E9E2] shadow-[0_30px_80px_-40px_rgba(26,15,18,0.9)]">
        <div class="pointer-events-none absolute -right-24 -top-24 h-72 w-72 rounded-full bg-[#B8893B]/20 blur-3xl" aria-hidden="true"></div>
        <div class="pointer-events-none absolute -bottom-28 left-10 h-64 w-64 rounded-full bg-[#7FA35B]/10 blur-3xl" aria-hidden="true"></div>
        <span class="pointer-events-none absolute inset-x-10 top-0 h-px bg-gradient-to-r from-transparent via-gold/60 to-transparent" aria-hidden="true"></span>

        <div class="relative grid gap-10 px-6 py-12 sm:px-10 lg:grid-cols-[1.3fr_1fr_1fr_1fr] lg:gap-8 lg:py-14">
            <div class="max-w-sm">
                <x-logo variant="full" tone="light" size="lg" />
                <p class="mt-6 font-serif text-2xl italic leading-snug text-[#FFF8EF]">“A reader lives a thousand lives before he dies.”</p>
                <p class="mt-2 font-hand text-xl text-[#F2C38B]">— George R. R. Martin</p>
            </div>

            @auth
                @php
                    $footerColumns = [
                        'Explore' => [
                            ['Search books', route('books.index')],
                            ['Reading feed', route('feed.index')],
                            ['Quote wall', route('quotes.index')],
                            ['Buddy reads', route('buddy.index')],
                            ['Find readers', route('users.index')],
                        ],
                        'Your shelf' => [
                            ['My book tree', route('users.show', auth()->user()->username)],
                            [now()->year . ' in books', route('year.show', auth()->user()->username)],
                            ['Import from Goodreads', route('import.show')],
                            ['Profile settings', route('profile.edit')],
                        ],
                    ];
                @endphp
            @else
                @php
                    $footerColumns = [
                        'Explore' => [
                            ['How it works', route('home') . '#how-it-works'],
                            ['Join Bookworm', route('register')],
                            ['Log in', route('login')],
                        ],
                        'Your shelf' => [
                            ['Grow a book tree', route('register')],
                            ['Keep private notes', route('register')],
                            ['Save favorite quotes', route('register')],
                        ],
                    ];
                @endphp
            @endauth

            @foreach ($footerColumns as $heading => $links)
                <nav aria-label="{{ $heading }}">
                    <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[#F2C38B]/80">{{ $heading }}</p>
                    <ul class="mt-4 space-y-2.5">
                        @foreach ($links as [$label, $url])
                            <li><a href="{{ $url }}" class="focus-ring rounded text-sm text-[#F4E9E2]/80 transition hover:text-[#FFF8EF] hover:underline hover:decoration-gold/70 hover:underline-offset-4">{{ $label }}</a></li>
                        @endforeach
                    </ul>
                </nav>
            @endforeach

            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.22em] text-[#F2C38B]/80">Reading tonight?</p>
                <p class="mt-4 text-sm leading-6 text-[#F4E9E2]/75">Switch to the dark reading room, or head back to the top of the page.</p>
                <div class="mt-4 flex flex-wrap gap-2">
                    <button type="button" data-theme-toggle aria-pressed="false" aria-label="Switch to dark theme" class="focus-ring inline-flex items-center gap-2 rounded-full bg-white/10 px-4 py-2 text-sm font-semibold text-[#FFF8EF] ring-1 ring-inset ring-white/15 transition hover:bg-white/15">
                        <svg class="h-4 w-4 dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20.4 15.2A8.5 8.5 0 0 1 8.8 3.6 8.5 8.5 0 1 0 20.4 15.2Z"/></svg>
                        <svg class="hidden h-4 w-4 dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path stroke-linecap="round" d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/></svg>
                        <span class="dark:hidden">Dark mode</span><span class="hidden dark:inline">Light mode</span>
                    </button>
                    <a href="#top" class="focus-ring inline-flex items-center gap-2 rounded-full bg-[#FFF8EF] px-4 py-2 text-sm font-semibold text-burgundy-deep transition hover:-translate-y-0.5">
                        <span aria-hidden="true">&uarr;</span> Back to top
                    </a>
                </div>
            </div>
        </div>

        <div class="relative flex flex-col gap-2 border-t border-white/10 px-6 py-5 text-xs text-[#F4E9E2]/60 sm:flex-row sm:items-center sm:justify-between sm:px-10">
            <p>&copy; {{ now()->year }} Bookworm · For the love of stories, and the people who pass them along.</p>
            <p>Book data from <a href="https://openlibrary.org/" target="_blank" rel="noopener noreferrer" class="underline decoration-white/30 underline-offset-2 hover:text-[#FFF8EF]">Open Library</a></p>
        </div>
    </div>
</footer>
