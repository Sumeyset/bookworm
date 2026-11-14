{{--
    Site header: a floating wine-glass bar.
    - sliding highlight behind the nav links (Alpine)
    - search palette (Ctrl/⌘+K or "/"), see resources/js/app.js
    - "Now reading" chip with a progress ring
    - a gold line along the bottom that fills as you scroll the page
--}}
@php
    $readingNow = auth()->check()
        ? auth()->user()->shelfItems()->with('book')->where('status', 'reading')->latest('updated_at')->first()
        : null;
    $readingPercent = $readingNow?->progressPercent() ?? 0;
    $unreadCount = auth()->check() ? auth()->user()->unreadNotifications()->count() : 0;
    $unreadMessages = auth()->check() ? auth()->user()->unreadConversationCount() + auth()->user()->messageRequestCount() : 0;
    $recentNotifications = auth()->check() ? auth()->user()->notifications()->take(6)->get() : collect();
@endphp

<header id="site-header" class="sticky top-0 z-50 px-3 pt-3 sm:px-5"
        x-data="{ scrolled: false, progress: 0, update() { const max = document.documentElement.scrollHeight - innerHeight; this.scrolled = scrollY > 8; this.progress = max > 0 ? Math.min(100, scrollY / max * 100) : 0; } }"
        x-init="update()" @scroll.window.passive="update()" @resize.window.passive="update()">
    <div class="bw-header relative mx-auto max-w-7xl rounded-[1.4rem] text-[#FFF8EF] transition-all duration-500"
         :class="scrolled ? 'bw-header--scrolled' : ''">
        <span class="bw-header__shine" aria-hidden="true"></span>

        <div class="relative flex items-center justify-between gap-3 px-3 transition-all duration-500 sm:px-4"
             :class="scrolled ? 'py-1.5' : 'py-2.5'">
            <a href="{{ route('home') }}" aria-label="Bookworm home" class="focus-ring group/logo shrink-0 rounded-2xl">
                <x-logo variant="full" tone="light" class="bw-header__logo" />
            </a>

            @auth
                @php
                    $navigationItems = [
                        ['label' => 'Books', 'url' => route('books.index'), 'active' => request()->routeIs('books.*'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 4.75A2.75 2.75 0 0 1 7.75 2H20v17H7.75A2.75 2.75 0 0 0 5 21.75v-17Z"/><path stroke-linecap="round" d="M5 18.5A2.5 2.5 0 0 1 7.5 16H20"/>'],
                        ['label' => 'My tree', 'url' => route('users.show', Auth::user()->username), 'active' => request()->routeIs('users.show') && Auth::user()->username === request()->route('user')?->username, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-7m0 0c-3.9 0-7-2.7-7-6.2C5 4.6 8.1 2 12 2s7 2.6 7 5.8c0 3.5-3.1 6.2-7 6.2Zm0 0-3-3m3 3 3-3"/>'],
                        ['label' => 'Feed', 'url' => route('feed.index'), 'active' => request()->routeIs('feed.*'), 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" d="M5 5.5h14M5 12h10M5 18.5h7"/><circle cx="18" cy="17" r="3"/>'],
                        ['label' => 'Readers', 'url' => route('users.index'), 'active' => request()->routeIs('users.index'), 'icon' => '<circle cx="9" cy="8" r="3.2"/><path stroke-linecap="round" stroke-linejoin="round" d="M3.5 19a5.5 5.5 0 0 1 11 0m2-8.5a3 3 0 1 0 0-6m1 9a5 5 0 0 1 3 4.5"/>'],
                    ];
                @endphp

                {{-- Nav with a highlight that glides to the hovered link --}}
                <nav class="relative hidden items-center rounded-full bg-black/20 p-1 ring-1 ring-inset ring-white/10 md:flex" aria-label="Main navigation"
                     x-data="{ x: 0, w: 0, ready: false, move(el) { if (!el) return; this.x = el.offsetLeft; this.w = el.offsetWidth; this.ready = true; }, home() { this.move($el.querySelector('[aria-current=page]')); if (!$el.querySelector('[aria-current=page]')) this.w = 0; } }"
                     x-init="$nextTick(() => home())" @mouseleave="home()" @resize.window.debounce.100ms="home()">
                    <span class="bw-nav__pill" :class="ready ? 'opacity-100' : 'opacity-0'" :style="`transform: translateX(${x}px); width: ${w}px`" aria-hidden="true"></span>
                    @foreach ($navigationItems as $item)
                        <a href="{{ $item['url'] }}"
                           @if ($item['active']) aria-current="page" @endif
                           @mouseenter="move($el)" @focus="move($el)"
                           class="bw-nav__link focus-ring">
                            <svg class="h-4 w-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" aria-hidden="true">{!! $item['icon'] !!}</svg>
                            <span>{{ $item['label'] }}</span>
                        </a>
                    @endforeach
                </nav>

                <div class="flex shrink-0 items-center gap-1.5">
                    {{-- Search palette trigger --}}
                    <button type="button" data-open-palette class="focus-ring group/search inline-flex h-10 items-center gap-2 rounded-full bg-white/10 pl-3 pr-3 text-sm text-[#FFF8EF]/80 ring-1 ring-inset ring-white/15 transition hover:bg-white/15 hover:text-[#FFF8EF] xl:w-48 xl:pr-2" aria-label="Search and jump (Ctrl K)">
                        <svg class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.5 3a5.5 5.5 0 1 0 3.46 9.78l3.63 3.63a.75.75 0 1 0 1.06-1.06l-3.63-3.63A5.5 5.5 0 0 0 8.5 3ZM4.5 8.5a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd"/></svg>
                        <span class="hidden flex-1 text-left xl:inline">Search…</span>
                        <kbd class="hidden rounded-md bg-black/25 px-1.5 py-0.5 font-sans text-[10px] font-semibold text-[#F1D9C4] xl:inline" data-kbd-hint>Ctrl K</kbd>
                    </button>

                    {{-- Now reading --}}
                    @if ($readingNow)
                        <a href="{{ route('books.show', $readingNow->book) }}" class="focus-ring group/now hidden items-center gap-2.5 rounded-full bg-black/15 py-1 pl-1 pr-3.5 ring-1 ring-inset ring-white/10 transition hover:bg-black/25 2xl:inline-flex" title="Continue reading {{ $readingNow->book->title }}">
                            <span class="relative h-9 w-9 shrink-0">
                                <svg class="absolute inset-0 h-9 w-9 -rotate-90" viewBox="0 0 36 36" aria-hidden="true">
                                    <circle cx="18" cy="18" r="16" fill="none" stroke="rgba(255,255,255,0.15)" stroke-width="2.5"/>
                                    <circle cx="18" cy="18" r="16" fill="none" stroke="#E9C27D" stroke-width="2.5" stroke-linecap="round" pathLength="100" stroke-dasharray="{{ max(2, $readingPercent) }} 100"/>
                                </svg>
                                @if ($readingNow->book->cover_url)
                                    <img src="{{ $readingNow->book->cover_src }}" alt="" class="absolute inset-[5px] h-[26px] w-[26px] rounded-full object-cover" loading="lazy">
                                @else
                                    <span class="absolute inset-[5px] flex items-center justify-center rounded-full bg-burgundy font-serif text-[10px] font-bold">{{ Str::upper(Str::substr($readingNow->book->title, 0, 1)) }}</span>
                                @endif
                            </span>
                            <span class="min-w-0 leading-tight">
                                <span class="block text-[10px] font-semibold uppercase tracking-[0.18em] text-[#F2C38B]/90">Now reading · {{ $readingPercent }}%</span>
                                <span class="block max-w-[9rem] truncate text-xs font-semibold text-[#FFF8EF]">{{ $readingNow->book->title }}</span>
                            </span>
                        </a>
                    @endif

                    <a href="{{ route('books.index') }}" class="focus-ring hidden h-10 items-center gap-1.5 rounded-full bg-[#FFF8EF] px-4 text-sm font-semibold text-burgundy-deep shadow-[0_8px_20px_-10px_rgba(0,0,0,0.6)] transition hover:-translate-y-0.5 hover:bg-white lg:inline-flex">
                        <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 4a1 1 0 0 1 1 1v4h4a1 1 0 1 1 0 2h-4v4a1 1 0 1 1-2 0v-4H5a1 1 0 1 1 0-2h4V5a1 1 0 0 1 1-1Z"/></svg>
                        Add a book
                    </a>

                    {{-- Messages --}}
                    <a href="{{ route('messages.index') }}" class="focus-ring relative flex h-10 w-10 items-center justify-center rounded-full text-[#fff8ef] transition hover:bg-white/10" aria-label="Messages{{ $unreadMessages ? ' (' . $unreadMessages . ' unread)' : '' }}" title="Messages">
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round" aria-hidden="true"><path d="M21.5 3.5 10.6 13.4M21.5 3.5 14.6 21l-4-7.6-7.6-4 18.5-5.9Z"/></svg>
                        <span data-dm-badge @if (! $unreadMessages) hidden @endif class="absolute right-0.5 top-0.5 flex h-4 min-w-4 items-center justify-center rounded-full bg-[#F2C38B] px-1 text-[10px] font-bold text-burgundy-deep ring-2 ring-burgundy-deep">{{ $unreadMessages > 9 ? '9+' : $unreadMessages }}</span>
                    </a>

                    {{-- Notifications --}}
                    <details class="group/bell relative">
                        <summary class="focus-ring relative flex h-10 w-10 cursor-pointer list-none items-center justify-center rounded-full text-[#fff8ef] transition hover:bg-white/10 [&::-webkit-details-marker]:hidden" aria-label="Notifications{{ $unreadCount ? ' (' . $unreadCount . ' unread)' : '' }}">
                            <svg class="h-5 w-5 {{ $unreadCount ? 'animate-[bw-ring_2.4s_ease-in-out_1]' : '' }}" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 16V11a6 6 0 1 1 12 0v5l1.5 2h-15L6 16Zm4 4a2 2 0 0 0 4 0"/></svg>
                            @if ($unreadCount)
                                <span class="absolute right-1 top-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-[#F2C38B] px-1 text-[10px] font-bold text-burgundy-deep ring-2 ring-burgundy-deep">{{ $unreadCount > 9 ? '9+' : $unreadCount }}</span>
                            @endif
                        </summary>
                        <div class="absolute right-0 z-[100] mt-3 w-[22rem] max-w-[calc(100vw-1.5rem)] overflow-hidden rounded-3xl border border-warmborder bg-paper p-2 text-ink shadow-[0_24px_60px_-20px_rgba(26,15,18,0.55)] dark:border-[#4B3035] dark:bg-raised dark:text-[#F4E9E2]">
                            <div class="flex items-center justify-between px-3 py-2">
                                <p class="font-display text-2xl font-bold">Notifications</p>
                                @if ($unreadCount)
                                    <form method="POST" action="{{ route('notifications.readAll') }}">@csrf<button class="text-xs font-semibold text-burgundy underline dark:text-[#E9A2AE]">Mark all read</button></form>
                                @endif
                            </div>
                            @forelse ($recentNotifications as $notification)
                                @include('notifications.partials.item')
                            @empty
                                <p class="px-3 py-6 text-center text-sm text-tan">Nothing new yet.</p>
                            @endforelse
                            <a href="{{ route('notifications.index') }}" class="mt-1 block rounded-xl px-3 py-2 text-center text-sm font-semibold text-burgundy hover:bg-parchment dark:text-[#E9A2AE] dark:hover:bg-white/5">See all</a>
                        </div>
                    </details>

                    {{-- On phones the theme switch lives in the account menu, so the bar fits. --}}
                    <span class="hidden sm:inline-flex">@include('layouts.partials.theme-toggle')</span>

                    <details class="group relative">
                        <summary class="focus-ring flex cursor-pointer list-none items-center gap-1.5 rounded-full p-0.5 pr-1.5 text-sm font-medium text-[#fff8ef] transition hover:bg-white/10 [&::-webkit-details-marker]:hidden">
                            <x-avatar :src="Auth::user()->profile_photo_url" :name="Auth::user()->name" size="sm" tone="light" />
                            <svg class="h-4 w-4 text-gold transition group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 7.22a.75.75 0 0 1 1.06 0L10 10.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                        </summary>
                        <div class="absolute right-0 z-[100] mt-3 w-72 origin-top-right overflow-hidden rounded-3xl border border-warmborder bg-paper p-2 text-ink shadow-[0_24px_60px_-20px_rgba(26,15,18,0.55)] dark:border-[#4B3035] dark:bg-raised dark:text-[#F4E9E2]">
                            <div class="relative overflow-hidden rounded-2xl bg-wine-header px-4 py-4 text-[#FFF8EF]">
                                <span class="pointer-events-none absolute -right-6 -top-8 h-24 w-24 rounded-full bg-gold/25 blur-2xl" aria-hidden="true"></span>
                                <div class="relative flex items-center gap-3">
                                    <x-avatar :src="Auth::user()->profile_photo_url" :name="Auth::user()->name" size="sm" tone="light" />
                                    <div class="min-w-0">
                                        <p class="truncate font-display text-2xl font-bold leading-none">{{ Auth::user()->name }}</p>
                                        <p class="mt-1 truncate text-xs text-[#F1D9C4]/80">{{ '@' . Auth::user()->username }}</p>
                                    </div>
                                </div>
                            </div>
                            @foreach ([
                                ['My book tree', route('users.show', Auth::user()->username)],
                                [now()->year . ' in books', route('year.show', Auth::user()->username)],
                                ['Buddy reads', route('buddy.index')],
                                ['Quote wall', route('quotes.index')],
                                ['Import from Goodreads', route('import.show')],
                                ['Reading preferences', route('onboarding.show')],
                                ['Profile settings', route('profile.edit')],
                            ] as [$label, $url])
                                <a href="{{ $url }}" class="focus-ring group/item mt-1 flex items-center justify-between rounded-xl px-3 py-2.5 text-sm text-walnut transition hover:bg-parchment hover:text-burgundy dark:text-[#e3d3ca] dark:hover:bg-[#382329] dark:hover:text-[#F4E9E2]">{{ $label }} <span aria-hidden="true" class="text-tan transition group-hover/item:translate-x-0.5">&rarr;</span></a>
                            @endforeach
                            <button type="button" data-theme-toggle class="focus-ring mt-1 flex w-full items-center justify-between rounded-xl px-3 py-2.5 text-sm text-walnut transition hover:bg-parchment hover:text-burgundy dark:text-[#e3d3ca] dark:hover:bg-[#382329] dark:hover:text-[#F4E9E2] sm:hidden">
                                <span class="dark:hidden">Dark mode</span><span class="hidden dark:inline">Light mode</span>
                                <span aria-hidden="true">🌙</span>
                            </button>
                            <form method="POST" action="{{ route('logout') }}" class="mt-1 border-t border-warmborder pt-1 dark:border-[#4B3035]">
                                @csrf
                                <button type="submit" class="focus-ring block w-full rounded-xl px-3 py-2.5 text-left text-sm font-semibold text-burgundy transition hover:bg-rosetint dark:text-[#e7a8b2] dark:hover:bg-[#382329]">Log out</button>
                            </form>
                        </div>
                    </details>
                </div>
            @else
                <div class="flex items-center gap-1.5">
                    @include('layouts.partials.theme-toggle')
                    <a href="{{ route('login') }}" class="focus-ring hidden rounded-full px-4 py-2 text-sm font-semibold text-[#fff8ef] transition hover:bg-white/10 sm:inline-flex">Log in</a>
                    <a href="{{ route('register') }}" class="focus-ring rounded-full bg-[#fff8ef] px-4 py-2 text-sm font-semibold text-burgundy-deep shadow-sm transition hover:-translate-y-0.5 hover:bg-white">Join Bookworm</a>
                </div>
            @endauth
        </div>

        {{-- Page progress: a gold line that fills as you read down the page --}}
        <span class="bw-header__progress" aria-hidden="true"><span :style="`width: ${progress}%`"></span></span>
    </div>
</header>

@auth
    {{-- Search palette --}}
    <dialog id="command-palette" class="bw-palette" aria-label="Search and jump">
        <form method="GET" action="{{ route('books.index') }}" class="flex items-center gap-3 border-b border-warmborder px-5 py-4 dark:border-[#4B3035]" data-palette-form>
            <svg class="h-5 w-5 shrink-0 text-tan" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.5 3a5.5 5.5 0 1 0 3.46 9.78l3.63 3.63a.75.75 0 1 0 1.06-1.06l-3.63-3.63A5.5 5.5 0 0 0 8.5 3ZM4.5 8.5a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd"/></svg>
            <label for="palette-input" class="sr-only">Search readers and books, or jump to a page</label>
            <input id="palette-input" name="q" type="search" autocomplete="off" placeholder="Search readers and books, or jump to a page…" class="min-w-0 flex-1 border-0 bg-transparent p-0 text-lg text-ink placeholder:text-tan/70 focus:outline-none focus:ring-0 dark:text-[#F4E9E2]" data-palette-input>
            <kbd class="rounded-md border border-warmborder px-1.5 py-0.5 text-[10px] font-semibold text-tan dark:border-[#4B3035]">Esc</kbd>
        </form>
        <ul class="max-h-96 overflow-y-auto p-2" role="listbox" data-palette-list>
            <li data-palette-readers hidden></li>
            <li data-palette-search hidden>
                <button type="button" class="bw-palette__item" data-palette-submit>
                    <span class="bw-palette__icon">⌕</span>
                    <span>Search books for “<b data-palette-query></b>”</span>
                </button>
            </li>
            @foreach ([
                ['My book tree', route('users.show', Auth::user()->username), '🌳', 'tree shelf bookshelf my'],
                ['Search books', route('books.index'), '📚', 'books find add discover'],
                ['Reading feed', route('feed.index'), '✍️', 'activity feed reviews posts write'],
                ['Quote wall', route('quotes.index'), '❝', 'quotes wall passages'],
                ['Buddy reads', route('buddy.index'), '🤝', 'buddy read together club friends'],
                [now()->year . ' in books', route('year.show', Auth::user()->username), '✨', 'year recap wrapped stats'],
                ['Import from Goodreads', route('import.show'), '⬆️', 'import goodreads storygraph csv'],
                ['Notifications', route('notifications.index'), '🔔', 'notifications alerts'],
                ['Messages', route('messages.index'), '✉️', 'messages dm direct chat inbox send'],
                ['Find readers', route('users.index'), '👋', 'readers people follow'],
                ['Reading preferences', route('onboarding.show'), '🎯', 'goal genres preferences'],
                ['Profile settings', route('profile.edit'), '⚙️', 'settings profile account password bio'],
            ] as [$label, $url, $emoji, $keywords])
                <li data-palette-entry data-keywords="{{ Str::lower($label . ' ' . $keywords) }}">
                    <a href="{{ $url }}" class="bw-palette__item"><span class="bw-palette__icon">{{ $emoji }}</span><span>{{ $label }}</span><span class="ml-auto text-xs text-tan">Jump</span></a>
                </li>
            @endforeach
            @if ($readingNow)
                <li data-palette-entry data-keywords="{{ Str::lower('continue reading now ' . $readingNow->book->title) }}">
                    <a href="{{ route('books.show', $readingNow->book) }}" class="bw-palette__item"><span class="bw-palette__icon">🔖</span><span>Continue “{{ $readingNow->book->title }}”</span><span class="ml-auto text-xs text-tan">{{ $readingPercent }}%</span></a>
                </li>
            @endif
        </ul>
        <p class="border-t border-warmborder px-5 py-2.5 text-xs text-tan dark:border-[#4B3035]">Tip: press <kbd class="font-semibold">/</kbd> or <kbd class="font-semibold" data-kbd-hint>Ctrl K</kbd> anywhere to open this.</p>
    </dialog>
@endauth
