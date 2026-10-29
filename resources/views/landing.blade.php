<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#F7F1EA">
    <title>Bookworm — Your reading life, beautifully shelved</title>
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
<body class="bg-ivory font-sans text-ink antialiased dark:bg-wineblack dark:text-[#F4E9E2]">
    <div class="paper-grain min-h-screen">
        <header class="sticky top-0 z-30 border-b border-warmborder/80 bg-paper/95 backdrop-blur-sm dark:border-[#4B3035] dark:bg-raised/95">
            <div class="mx-auto flex max-w-7xl items-center justify-between gap-5 px-4 py-3 sm:px-8">
                <a href="{{ route('home') }}" aria-label="Bookworm home" class="focus-ring group/logo rounded-xl">
                    <x-logo variant="full" tone="dark" class="h-14 w-auto transition-transform duration-300 group-hover/logo:scale-105 group-focus-visible/logo:scale-105 motion-reduce:transition-none" />
                </a>
                <nav class="flex items-center gap-2" aria-label="Main navigation">
                    <button type="button" data-theme-toggle aria-pressed="false" aria-label="Switch to dark theme" class="focus-ring inline-flex h-10 w-10 items-center justify-center rounded-full border border-warmborder text-burgundy transition hover:bg-parchment dark:border-[#4B3035] dark:text-[#F4E9E2] dark:hover:bg-[#382329]">
                        <svg class="h-5 w-5 dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path stroke-linecap="round" d="M12 2v2m0 16v2M4.93 4.93l1.42 1.42m11.3 11.3 1.42 1.42M2 12h2m16 0h2M4.93 19.07l1.42-1.42m11.3-11.3 1.42-1.42"/></svg>
                        <svg class="hidden h-5 w-5 dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M20.4 15.2A8.5 8.5 0 0 1 8.8 3.6 8.5 8.5 0 1 0 20.4 15.2Z"/></svg>
                    </button>
                    @auth
                        <a href="{{ route('books.index') }}" class="focus-ring rounded-full px-4 py-2 text-sm font-semibold text-burgundy hover:bg-rosetint dark:text-[#F4E9E2] dark:hover:bg-[#382329]">Explore books</a>
                        <a href="{{ route('users.show', Auth::user()->username) }}" class="focus-ring rounded-full bg-burgundy px-5 py-2.5 text-sm font-semibold text-[#FFF8EF] transition hover:bg-burgundy-hover">My bookshelf</a>
                    @else
                        <a href="{{ route('login') }}" class="focus-ring rounded-full px-4 py-2 text-sm font-semibold text-burgundy hover:bg-rosetint dark:text-[#F4E9E2] dark:hover:bg-[#382329]">Log in</a>
                        <a href="{{ route('register') }}" class="focus-ring rounded-full bg-burgundy px-5 py-2.5 text-sm font-semibold text-[#FFF8EF] transition hover:bg-burgundy-hover">Join Bookworm</a>
                    @endauth
                </nav>
            </div>
        </header>

        <main>
            <section class="mx-auto grid max-w-7xl items-center gap-12 px-5 py-16 sm:px-8 sm:py-24 lg:grid-cols-[1.05fr_0.95fr] lg:gap-16 lg:px-12 lg:py-28">
                <div>
                    <p class="flex items-center gap-3 text-xs font-semibold uppercase tracking-[0.22em] text-tan dark:text-[#c7a491]">
                        <span class="h-px w-8 bg-gold"></span> A quieter corner of the internet
                    </p>
                    <h1 class="mt-6 max-w-2xl font-serif text-5xl font-medium leading-[1.08] tracking-tight text-ink dark:text-[#F4E9E2] sm:text-6xl lg:text-7xl">
                        Your reading life,<br><span class="italic text-burgundy dark:text-burgundy-dark">beautifully shelved.</span>
                    </h1>
                    <p class="mt-6 max-w-xl text-lg leading-8 text-walnut dark:text-[#d4c2b9]">Keep the books that stay with you. Rate a favorite, leave a note in the margin, and find readers who get it.</p>
                    <div class="mt-8 flex flex-wrap items-center gap-4">
                        @auth
                            <a href="{{ route('books.index') }}" class="focus-ring rounded-full bg-burgundy px-6 py-3 text-sm font-semibold text-[#FFF8EF] shadow-sm transition hover:-translate-y-0.5 hover:bg-burgundy-hover">Find your next read</a>
                            <a href="{{ route('users.index') }}" class="focus-ring rounded-full px-4 py-3 text-sm font-semibold text-burgundy hover:bg-rosetint dark:text-[#F4E9E2] dark:hover:bg-[#382329]">Meet the readers <span aria-hidden="true">&rarr;</span></a>
                        @else
                            <a href="{{ route('register') }}" class="focus-ring rounded-full bg-burgundy px-6 py-3 text-sm font-semibold text-[#FFF8EF] shadow-sm transition hover:-translate-y-0.5 hover:bg-burgundy-hover">Get started free</a>
                            <a href="#how-it-works" class="focus-ring rounded-full px-4 py-3 text-sm font-semibold text-burgundy hover:bg-rosetint dark:text-[#F4E9E2] dark:hover:bg-[#382329]">See how it works <span aria-hidden="true">&darr;</span></a>
                        @endauth
                    </div>
                    <p class="mt-5 text-xs text-tan dark:text-[#c7a491]">A shelf of your own. No rush, no reading challenge required.</p>
                </div>

                <div class="relative mx-auto flex min-h-[26rem] w-full max-w-lg items-center justify-center" aria-label="A small arrangement of favorite books">
                    <div class="absolute inset-x-7 bottom-8 h-5 rounded-sm bg-gradient-to-b from-[#9B7356] to-[#5A3A2E] shadow-[0_16px_22px_-12px_rgba(42,26,29,0.55)]"></div>
                    <article class="absolute left-[8%] top-[15%] z-10 flex h-60 w-40 -rotate-6 flex-col justify-between rounded-r-sm border-l-4 border-[#E6D9D0]/80 bg-gradient-to-br from-burgundy via-[#8B2A3D] to-burgundy-deep p-4 text-[#FFF8EF] shadow-[0_22px_32px_-18px_rgba(42,26,29,0.6)] transition-transform duration-200 hover:-translate-y-2 hover:rotate-0">
                        <span class="text-center font-serif text-xs uppercase tracking-[0.18em] text-[#E8CFA6]">Jane Austen</span>
                        <span class="text-center font-serif text-2xl leading-tight">Pride &amp;<br>Prejudice</span>
                        <span class="text-center text-[10px] uppercase tracking-[0.2em] text-[#E8CFA6]">A classic, still</span>
                    </article>
                    <article class="absolute left-[35%] top-[7%] z-20 flex h-72 w-44 flex-col justify-between rounded-r-sm border-l-4 border-[#E6D9D0]/80 bg-gradient-to-br from-[#63442D] via-[#8A6A55] to-[#3B2418] p-4 text-[#FFF8EF] shadow-[0_24px_36px_-18px_rgba(42,26,29,0.65)] transition-transform duration-200 hover:-translate-y-2">
                        <span class="text-center font-serif text-xs uppercase tracking-[0.18em] text-[#E8CFA6]">J. R. R. Tolkien</span>
                        <span class="text-center font-serif text-3xl leading-tight">The<br>Hobbit</span>
                        <span class="text-center text-[10px] uppercase tracking-[0.2em] text-[#E8CFA6]">There and back again</span>
                    </article>
                    <article class="absolute right-[5%] top-[19%] z-10 flex h-56 w-36 rotate-6 flex-col justify-between rounded-r-sm border-l-4 border-[#E6D9D0]/80 bg-gradient-to-br from-[#54382A] via-[#72513A] to-[#3B2418] p-3 text-[#FFF8EF] shadow-[0_20px_30px_-18px_rgba(42,26,29,0.6)] transition-transform duration-200 hover:-translate-y-2 hover:rotate-0">
                        <span class="text-center font-serif text-[10px] uppercase tracking-[0.16em] text-[#E8CFA6]">Louisa May Alcott</span>
                        <span class="text-center font-serif text-2xl">Little<br>Women</span>
                        <span class="text-center text-[10px] uppercase tracking-[0.2em] text-[#E8CFA6]">Since 1868</span>
                    </article>
                    <blockquote class="absolute bottom-0 right-[2%] z-30 max-w-[17rem] rotate-2 border border-warmborder bg-paper px-5 py-4 shadow-bookplate dark:border-[#4B3035] dark:bg-raised">
                        <span class="font-serif text-4xl leading-none text-burgundy dark:text-burgundy-dark" aria-hidden="true">“</span>
                        <p class="-mt-2 font-serif text-base italic leading-6 text-ink dark:text-[#F4E9E2]">A book is a door you can open again.</p>
                        <cite class="mt-2 block text-right text-xs not-italic text-tan dark:text-[#c7a491]">— your reading journal</cite>
                    </blockquote>
                </div>
            </section>

            <section id="how-it-works" class="border-y border-warmborder bg-paper/75 py-16 dark:border-[#4B3035] dark:bg-raised/60 sm:py-20">
                <div class="mx-auto max-w-7xl px-5 sm:px-8 lg:px-12">
                    <div class="max-w-2xl">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-tan dark:text-[#c7a491]">A reader’s kind of social</p>
                        <h2 class="mt-3 font-serif text-3xl font-semibold text-ink dark:text-[#F4E9E2] sm:text-4xl">Make room for the books that matter.</h2>
                    </div>
                    <div class="mt-10 grid gap-8 md:grid-cols-3">
                        <article class="border-t border-gold/50 pt-5">
                            <p class="font-serif text-4xl text-burgundy dark:text-burgundy-dark">01</p>
                            <h3 class="mt-4 font-serif text-xl font-semibold text-ink dark:text-[#F4E9E2]">Keep your shelf</h3>
                            <p class="mt-2 text-sm leading-6 text-walnut dark:text-[#d4c2b9]">Find a title, give it a star or five, and remember what you thought when you turned the last page.</p>
                        </article>
                        <article class="border-t border-gold/50 pt-5 md:mt-8">
                            <p class="font-serif text-4xl text-burgundy dark:text-burgundy-dark">02</p>
                            <h3 class="mt-4 font-serif text-xl font-semibold text-ink dark:text-[#F4E9E2]">Read with company</h3>
                            <p class="mt-2 text-sm leading-6 text-walnut dark:text-[#d4c2b9]">Follow readers whose reviews make you pause, smile, or add another book to your list.</p>
                        </article>
                        <article class="border-t border-gold/50 pt-5">
                            <p class="font-serif text-4xl text-burgundy dark:text-burgundy-dark">03</p>
                            <h3 class="mt-4 font-serif text-xl font-semibold text-ink dark:text-[#F4E9E2]">Keep the good lines</h3>
                            <p class="mt-2 text-sm leading-6 text-walnut dark:text-[#d4c2b9]">Bring along a personal PDF and mark the passages you want to revisit.</p>
                        </article>
                    </div>
                </div>
            </section>

            <section class="mx-auto grid max-w-7xl items-center gap-10 px-5 py-16 sm:px-8 sm:py-20 lg:grid-cols-[0.9fr_1.1fr] lg:px-12">
                <div>
                    <p class="text-xs font-semibold uppercase tracking-[0.2em] text-tan dark:text-[#c7a491]">The reading room</p>
                    <h2 class="mt-3 font-serif text-3xl font-semibold text-ink dark:text-[#F4E9E2] sm:text-4xl">Good stories make good company.</h2>
                    <p class="mt-4 max-w-lg text-sm leading-7 text-walnut dark:text-[#d4c2b9]">Your profile is a living shelf: reviews, readers you follow, and the books you’ve decided to keep close.</p>
                    @guest
                        <a href="{{ route('register') }}" class="focus-ring mt-6 inline-flex rounded-full bg-burgundy px-5 py-3 text-sm font-semibold text-[#FFF8EF] transition hover:bg-burgundy-hover">Start your journal</a>
                    @endguest
                </div>
                <div class="bookplate overflow-hidden rounded-xl border-[10px] border-leather bg-leather p-5 shadow-shelf sm:border-[14px]">
                    <div class="flex items-center justify-between border-b border-[#E8CFA6]/40 pb-4 text-[#FFF8EF]">
                        <div>
                            <p class="font-serif text-xl">Mara’s reading shelf</p>
                            <p class="mt-1 text-xs text-[#E8CFA6]">Three books, three good reasons</p>
                        </div>
                        <span class="font-hand text-2xl text-[#D1A24E]">since 2021</span>
                    </div>
                    <div class="flex h-52 items-end justify-center gap-2 pt-5" aria-label="Sample book spines">
                        <div class="h-36 w-12 rounded-t-sm border-l-4 border-[#E6D9D0]/70 bg-gradient-to-r from-[#4A1220] via-[#8B2A3D] to-[#4A1220] shadow-lg"></div>
                        <div class="h-44 w-14 rounded-t-sm border-l-4 border-[#E6D9D0]/70 bg-gradient-to-r from-[#3B2418] via-[#72513a] to-[#493023] shadow-lg"></div>
                        <div class="h-40 w-12 rounded-t-sm border-l-4 border-[#E6D9D0]/70 bg-gradient-to-r from-[#54382A] via-[#8A6A55] to-[#3B2418] shadow-lg"></div>
                        <div class="h-32 w-14 rounded-t-sm border-l-4 border-[#E6D9D0]/70 bg-gradient-to-r from-[#4A1220] via-[#69323a] to-[#3B2418] shadow-lg"></div>
                    </div>
                    <div class="h-3 border-y border-[#E8CFA6]/70 bg-gradient-to-b from-[#B8893B] to-[#79543d]"></div>
                </div>
            </section>

            <section class="bg-parchment/70 py-14 dark:bg-[#211317]">
                <div class="mx-auto flex max-w-7xl flex-col gap-5 px-5 sm:px-8 md:flex-row md:items-center md:justify-between lg:px-12">
                    <div>
                        <p class="font-serif text-2xl italic text-ink dark:text-[#F4E9E2]">“The right book finds you at the right time.”</p>
                        <p class="mt-2 text-sm text-tan dark:text-[#c7a491]">A small reminder from one reader to another.</p>
                    </div>
                    <p class="font-hand text-2xl text-burgundy dark:text-burgundy-dark">— the Bookworm readers</p>
                </div>
            </section>

            <section class="mx-auto max-w-7xl px-5 py-16 sm:px-8 sm:py-20 lg:px-12">
                <div class="flex flex-col items-start justify-between gap-6 rounded-xl border border-burgundy/15 bg-paper p-7 shadow-bookplate dark:border-burgundy-dark/30 dark:bg-raised sm:flex-row sm:items-center sm:p-10">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-tan dark:text-[#c7a491]">A little home for stories</p>
                        <h2 class="mt-3 font-serif text-3xl font-semibold text-ink dark:text-[#F4E9E2]">Free to read. Free to remember.</h2>
                        <p class="mt-2 text-sm text-walnut dark:text-[#d4c2b9]">Make an account and start your shelf today.</p>
                    </div>
                    <a href="{{ route('register') }}" class="focus-ring inline-flex shrink-0 rounded-full bg-burgundy px-6 py-3 text-sm font-semibold text-[#FFF8EF] transition hover:bg-burgundy-hover">Join Bookworm</a>
                </div>
            </section>
        </main>

        <footer class="border-t border-warmborder bg-paper dark:border-[#4B3035] dark:bg-raised">
            <div class="mx-auto flex max-w-7xl flex-col gap-3 px-5 py-7 sm:flex-row sm:items-center sm:justify-between sm:px-8 lg:px-12">
                <x-logo variant="full" tone="dark" class="h-10 w-auto" />
                <p class="text-xs text-tan dark:text-[#c7a491]">For the love of stories, and the people who pass them along.</p>
            </div>
        </footer>
    </div>
</body>
</html>
