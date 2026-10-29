<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="theme-color" content="#4A1220">
        <title>{{ $title ?? 'Bookworm — a home for readers' }}</title>
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=caveat:500,600,700|figtree:400,500,600,700|lora:400,500,600,700,400i,500i&display=swap" rel="stylesheet">
        <script>
            (() => {
                const saved = localStorage.getItem('bookworm-theme');
                const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
                document.documentElement.classList.toggle('dark', saved ? saved === 'dark' : prefersDark);
            })();
        </script>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-ivory font-sans text-ink antialiased dark:bg-wineblack dark:text-[#F4E9E2]">
        <main class="paper-grain flex min-h-screen items-center justify-center px-4 py-8 sm:px-6 lg:px-8">
            <section class="relative grid w-full max-w-5xl overflow-hidden rounded-xl border border-warmborder bg-paper shadow-bookplate dark:border-[#4B3035] dark:bg-raised lg:min-h-[38rem] lg:grid-cols-[0.86fr_1.14fr]">
                <aside class="relative flex min-h-44 flex-col justify-between overflow-hidden bg-wine-header px-7 py-7 text-[#FFF8EF] sm:px-10 lg:min-h-[38rem] lg:px-12 lg:py-11">
                    <div class="pointer-events-none absolute inset-0 opacity-[0.08] [background-image:radial-gradient(rgba(255,239,216,0.9)_0.7px,transparent_0.7px)] [background-size:13px_13px]"></div>
                    <button type="button" data-theme-toggle aria-pressed="false" aria-label="Switch to dark theme" class="focus-ring absolute right-5 top-5 z-20 inline-flex h-10 w-10 items-center justify-center rounded-full border border-white/25 text-[#FFF8EF] transition hover:bg-white/10">
                        <svg class="h-5 w-5 dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path stroke-linecap="round" d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/></svg>
                        <svg class="hidden h-5 w-5 dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20.4 15.2A8.5 8.5 0 0 1 8.8 3.6 8.5 8.5 0 1 0 20.4 15.2Z"/></svg>
                    </button>
                    <a href="{{ route('home') }}" aria-label="Bookworm home" class="focus-ring group/logo relative z-10 inline-flex w-fit rounded-xl">
                        <x-logo variant="full" tone="light" class="h-14 w-auto transition-transform duration-300 group-hover/logo:scale-105 group-focus-visible/logo:scale-105 motion-reduce:transition-none" />
                    </a>
                    <div class="relative z-10 mt-8 hidden lg:block">
                        <p class="font-serif text-4xl leading-tight text-[#FFF8EF]">A good book<br>keeps good company.</p>
                        <p class="mt-4 max-w-xs text-sm leading-6 text-[#F4E9E2]/80">Find the next story, leave a thoughtful note, and keep the ones worth returning to.</p>
                        <div class="mt-12 flex h-28 items-end gap-2" aria-hidden="true">
                            <span class="h-20 w-10 -rotate-3 rounded-t-sm border-l-2 border-[#E6D9D0]/70 bg-gradient-to-r from-[#4A1220] via-[#8B2A3D] to-[#5A3A2E] shadow-lg"></span>
                            <span class="h-28 w-12 rotate-2 rounded-t-sm border-l-2 border-[#E6D9D0]/70 bg-gradient-to-r from-[#3B2418] via-[#72513a] to-[#493023] shadow-lg"></span>
                            <span class="h-24 w-10 -rotate-1 rounded-t-sm border-l-2 border-[#E6D9D0]/70 bg-gradient-to-r from-[#5A3A2E] via-[#79543d] to-[#3B2418] shadow-lg"></span>
                            <span class="ml-3 mb-2 font-hand text-2xl text-[#F4E9E2]">your next chapter</span>
                        </div>
                    </div>
                    <p class="relative z-10 mt-6 hidden text-xs tracking-wide text-[#F4E9E2]/60 lg:block">For people who always have one more page.</p>
                </aside>

                <section class="flex items-center px-6 py-9 sm:px-10 sm:py-12 lg:px-14">
                    <div class="mx-auto w-full max-w-md">
                        {{ $slot }}
                    </div>
                </section>
            </section>
        </main>
    </body>
</html>
