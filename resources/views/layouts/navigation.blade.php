<header class="relative z-50 border-b border-burgundy-deep/60 bg-wine-header text-[#FFF8EF] shadow-[0_12px_30px_-20px_rgba(26,15,18,0.8)]">
    <div class="pointer-events-none absolute inset-0 bg-[linear-gradient(90deg,transparent_49.8%,rgba(255,248,239,0.025)_50%,transparent_50.2%)] bg-[length:8px_100%] opacity-40"></div>
    <div class="relative mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-4 px-4 py-3.5 sm:px-8 sm:py-4 lg:flex-nowrap lg:px-12">
        <a href="{{ route('home') }}" aria-label="Bookworm home" class="focus-ring rounded-xl">
            <x-logo variant="full" tone="light" class="h-12 w-auto sm:h-14" />
        </a>

        @auth
            <nav class="order-3 flex w-full flex-wrap items-center gap-2 sm:justify-center lg:order-none lg:w-auto lg:flex-nowrap lg:justify-end" aria-label="Main navigation">
                @php
                    $navigationItems = [
                        ['label' => 'Books', 'route' => 'books.index', 'active' => request()->routeIs('books.*')],
                        ['label' => 'My bookshelf', 'route' => 'users.show', 'active' => request()->routeIs('users.show') && Auth::user()->username === request()->route('user')?->username],
                        ['label' => 'Activity', 'route' => 'feed.index', 'active' => request()->routeIs('feed.*')],
                        ['label' => 'Readers', 'route' => 'users.index', 'active' => request()->routeIs('users.index')],
                    ];
                @endphp
                @foreach ($navigationItems as $item)
                    <a href="{{ $item['route'] === 'users.show' ? route($item['route'], Auth::user()->username) : route($item['route']) }}"
                       @if ($item['active']) aria-current="page" @endif
                       class="{{ $item['active'] ? 'border-[#f4e9e2]/70 bg-[#fff8ef] text-burgundy-deep shadow-sm' : 'border-[#f1d9c4]/20 text-[#fff8ef] hover:border-[#f1d9c4]/50 hover:bg-white/10' }} focus-ring whitespace-nowrap rounded-full border px-3 py-2 text-xs font-semibold transition-colors duration-200 xl:px-4 xl:text-sm">
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>

            <div class="flex shrink-0 items-center gap-2">
                <button type="button" data-theme-toggle aria-pressed="false" aria-label="Switch to dark theme" class="focus-ring inline-flex h-10 w-10 items-center justify-center rounded-full border border-[#f1d9c4]/30 text-[#fff8ef] transition hover:bg-white/10">
                    <svg class="h-5 w-5 dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                        <circle cx="12" cy="12" r="4"/>
                        <path stroke-linecap="round" d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/>
                    </svg>
                    <svg class="hidden h-5 w-5 dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M20.4 15.2A8.5 8.5 0 0 1 8.8 3.6 8.5 8.5 0 1 0 20.4 15.2Z"/>
                    </svg>
                </button>

                <details class="group relative">
                    <summary class="focus-ring flex cursor-pointer list-none items-center gap-2 rounded-full border border-[#f1d9c4]/35 bg-white/[0.08] py-1.5 pl-1.5 pr-3 text-sm font-medium text-[#fff8ef] transition hover:bg-white/[0.14] [&::-webkit-details-marker]:hidden">
                        <x-avatar :src="Auth::user()->profile_photo_url" :name="Auth::user()->name" size="sm" tone="light" />
                        <span class="hidden max-w-28 truncate sm:inline">{{ Auth::user()->name }}</span>
                        <svg class="h-4 w-4 text-gold transition group-open:rotate-180" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.22 7.22a.75.75 0 0 1 1.06 0L10 10.94l3.72-3.72a.75.75 0 1 1 1.06 1.06l-4.25 4.25a.75.75 0 0 1-1.06 0L5.22 8.28a.75.75 0 0 1 0-1.06Z" clip-rule="evenodd"/></svg>
                    </summary>
                    <div class="absolute right-0 z-[100] mt-3 w-60 overflow-hidden rounded-xl border border-warmborder bg-paper p-2 text-ink shadow-bookplate dark:border-[#4B3035] dark:bg-raised dark:text-[#F4E9E2]">
                        <div class="border-b border-warmborder px-3 py-2 dark:border-[#4B3035]">
                            <p class="truncate text-sm font-semibold">{{ Auth::user()->name }}</p>
                            <p class="truncate text-xs text-tan dark:text-[#c7a491]">{{ Auth::user()->email }}</p>
                        </div>
                        <a href="{{ route('profile.edit') }}" class="focus-ring mt-1 block rounded-lg px-3 py-2 text-sm text-walnut transition hover:bg-parchment hover:text-burgundy dark:text-[#e3d3ca] dark:hover:bg-[#382329] dark:hover:text-[#F4E9E2]">Profile settings</a>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="focus-ring block w-full rounded-lg px-3 py-2 text-left text-sm text-burgundy transition hover:bg-rosetint dark:text-[#e7a8b2] dark:hover:bg-[#382329]">Log out</button>
                        </form>
                    </div>
                </details>
            </div>
        @else
            <div class="flex items-center gap-2">
                <a href="{{ route('login') }}" class="focus-ring rounded-full px-4 py-2 text-sm font-semibold text-[#fff8ef] transition hover:bg-white/10">Log in</a>
                <a href="{{ route('register') }}" class="focus-ring rounded-full bg-[#fff8ef] px-4 py-2 text-sm font-semibold text-burgundy-deep shadow-sm transition hover:bg-parchment">Join Bookworm</a>
            </div>
        @endauth
    </div>
    <div class="pointer-events-none absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-gold/70 to-transparent"></div>
</header>
