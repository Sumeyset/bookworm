<header class="relative z-50 overflow-visible border-b border-[#401b27]/45 bg-[radial-gradient(ellipse_at_18%_0%,rgba(204,154,104,0.2),transparent_38%),linear-gradient(105deg,#542331_0%,#672638_52%,#713345_100%)] text-[#fff8ef] shadow-[0_16px_40px_-22px_rgba(55,24,35,0.85)]">
    <div class="pointer-events-none absolute inset-0 opacity-[0.08] [background-image:radial-gradient(rgba(255,239,216,0.9)_0.7px,transparent_0.7px)] [background-size:13px_13px]"></div>
    <div class="relative mx-auto flex max-w-7xl items-center justify-between gap-2 px-3 py-7 sm:gap-4 sm:px-8 sm:py-8 lg:px-12">
        <a href="{{ route('home') }}" aria-label="Bookworm home" class="group flex min-w-0 items-center gap-2 rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#e6c795] sm:gap-4">
            <svg class="h-14 w-14 shrink-0 transition-transform duration-300 group-hover:-rotate-3 group-hover:scale-105 sm:h-20 sm:w-20" viewBox="0 0 140 140" fill="none" aria-hidden="true">
                <path d="M18 55c15-8 33-7 50 3v58c-17-9-35-10-50-3V55Z" fill="#F2DCC4"/>
                <path d="M122 55c-15-8-33-7-50 3v58c17-9 35-10 50-3V55Z" fill="#E3C5A5"/>
                <path d="M24 61c12-5 25-3 38 4v40c-13-6-26-8-38-4V61Z" fill="#FFF8EF"/>
                <path d="M116 61c-12-5-25-3-38 4v40c13-6 26-8 38-4V61Z" fill="#F6EBDD"/>
                <path d="M70 57v59" stroke="#B58771" stroke-width="3.5" stroke-linecap="round"/>
                <path d="M70 59c-8-7-7-15-2-20 5-4 12-7 11-14" stroke="#D39A6D" stroke-width="10" stroke-linecap="round"/>
                <circle cx="79" cy="19" r="11" fill="#D7A477"/>
                <circle cx="75.5" cy="16.5" r="1.7" fill="#392A2B"/>
                <circle cx="82.5" cy="16.5" r="1.7" fill="#392A2B"/>
                <path d="M76.5 22c1.5 1.5 4.5 1.5 6 0" stroke="#392A2B" stroke-width="1.5" stroke-linecap="round"/>
            </svg>
            <span class="min-w-0">
                <span class="block whitespace-nowrap font-handwritten text-4xl font-bold leading-none sm:text-6xl">Bookworm</span>
                <span class="mt-2 block h-px w-7 bg-[#e3bd82]/80" aria-hidden="true"></span>
                <span class="mt-1.5 block max-w-[15rem] font-serif text-[9px] italic leading-tight tracking-wide text-[#f0dbca]/90 sm:max-w-[20rem] sm:text-xs sm:leading-snug">
                    Find your next favorite book,<br class="sm:hidden"> and share the stories you love.
                </span>
            </span>
        </a>

        @auth
            <nav class="flex shrink-0 items-center gap-2 sm:gap-4" aria-label="Main navigation">
                <a href="{{ route('books.index') }}" class="rounded-full border border-[#f1d9c4]/20 px-4 py-2 text-sm font-semibold text-[#fff8ef] transition hover:border-[#f1d9c4]/45 hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#e6c795] sm:px-5">Books</a>
                <details class="group relative">
                    <summary class="flex cursor-pointer list-none items-center gap-2 rounded-full border border-[#f1d9c4]/35 bg-white/[0.08] py-2 pl-2 pr-3.5 text-sm font-medium text-[#fff8ef] transition hover:bg-white/[0.14] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#e6c795] [&::-webkit-details-marker]:hidden">
                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-[#e3bd82] font-semibold text-[#542331]">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                        <span class="hidden max-w-28 truncate sm:inline">{{ Auth::user()->name }}</span>
                        <svg class="h-4 w-4 text-[#e3bd82] transition group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 7.22a.75.75 0 0 1 1.06 0L10 10.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                    </summary>
                    <div class="absolute right-0 z-[100] mt-3 w-56 overflow-hidden rounded-2xl border border-[#d7c4b4] bg-[#fffaf4] p-2 text-[#4d3633] shadow-[0_18px_45px_-20px_rgba(65,35,35,0.5)]">
                        <div class="border-b border-[#eadfd4] px-3 py-2">
                            <p class="truncate text-sm font-semibold">{{ Auth::user()->name }}</p>
                            <p class="truncate text-xs text-[#8a756a]">{{ Auth::user()->email }}</p>
                        </div>
                        <a href="{{ route('profile.edit') }}" class="mt-1 block rounded-xl px-3 py-2 text-sm text-[#654c47] transition hover:bg-[#f3e9de] hover:text-[#672638]">Profile settings</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="block w-full rounded-xl px-3 py-2 text-left text-sm text-[#7a3948] transition hover:bg-[#f3e9de]">Log out</button>
                        </form>
                    </div>
                </details>
            </nav>
        @endauth
    </div>
    <div class="pointer-events-none absolute inset-x-0 bottom-0 h-[3px] bg-gradient-to-r from-transparent via-[#e3bd82]/90 to-transparent"></div>
</header>
