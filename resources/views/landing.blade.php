<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4A1220">
    <meta name="description" content="Bookworm grows a living tree from the books you read. Rate, keep notes, track your bookmark and share with fellow readers.">
    <title>Bookworm — Your reading life, beautifully shelved</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
    @include('layouts.partials.head-assets')
</head>
<body id="top" class="bg-ivory font-sans text-ink antialiased dark:bg-wineblack dark:text-[#F4E9E2]">
    <div class="paper-grain min-h-screen overflow-x-hidden">
        @include('layouts.navigation')

        <main>
            {{-- Hero --}}
            <section class="relative">
                <div class="pointer-events-none absolute -right-32 top-10 h-96 w-96 rounded-full bg-burgundy/10 blur-3xl dark:bg-burgundy-dark/20" aria-hidden="true"></div>
                <div class="pointer-events-none absolute -left-24 bottom-0 h-80 w-80 rounded-full bg-[#7FA35B]/15 blur-3xl" aria-hidden="true"></div>
                <div class="relative mx-auto grid max-w-7xl items-center gap-14 px-5 py-16 sm:px-8 sm:py-24 lg:grid-cols-[1.05fr_0.95fr] lg:px-12 lg:py-28">
                    <div>
                        <p class="inline-flex items-center gap-2 rounded-full border border-gold/40 bg-paper/70 px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.2em] text-tan dark:bg-white/5 dark:text-[#c7a491]">
                            <span class="h-1.5 w-1.5 rounded-full bg-[#7FA35B]"></span> A living library for readers
                        </p>
                        <h1 class="font-display mt-6 max-w-2xl text-5xl leading-[0.95] text-ink dark:text-[#F4E9E2] sm:text-6xl lg:text-7xl font-bold">
                            Your reading life,<br><span class="text-burgundy dark:text-burgundy-dark">beautifully shelved.</span>
                        </h1>
                        <p class="mt-6 max-w-xl text-lg leading-8 text-walnut dark:text-[#d4c2b9]">Find your next favorite book, and share the stories you love. Every book you shelve grows a branch on your own reading tree.</p>
                        <div class="mt-9 flex flex-wrap items-center gap-3">
                            <a href="{{ route('register') }}" class="btn-primary px-7 py-3.5 text-base">Join to explore books</a>
                            <a href="#how-it-works" class="btn-ghost px-6 py-3.5 text-base">See how it works <span aria-hidden="true">&darr;</span></a>
                        </div>
                        <p class="mt-5 text-sm text-tan dark:text-[#c7a491]">Free forever. No reading challenge required. Already a reader? <a href="{{ route('login') }}" class="font-semibold text-burgundy underline underline-offset-4 dark:text-burgundy-dark">Log in</a></p>
                    </div>

                    <div class="relative mx-auto w-full max-w-lg" aria-label="A small arrangement of favorite books">
                        <div class="relative overflow-hidden rounded-[2rem] border border-gold/30 bg-gradient-to-b from-[#fbf3e6] to-[#efdfc8] px-6 pb-0 pt-10 shadow-[0_40px_80px_-40px_rgba(70,35,20,0.6)] dark:from-[#211217] dark:to-[#2d171c]">
                            <span class="absolute right-8 top-6 h-14 w-14 rounded-full bg-[radial-gradient(circle_at_40%_40%,#fff6dc,#f2c97a_60%,transparent_72%)]" aria-hidden="true"></span>
                            <div class="flex items-end justify-center gap-3">
                                <article class="bookworm-cover-drift flex h-48 w-32 flex-col justify-between rounded-r-md border-l-4 border-[#E6D9D0]/70 bg-gradient-to-br from-burgundy via-[#8B2A3D] to-burgundy-deep p-3 text-center text-[#FFF8EF] shadow-xl sm:h-56 sm:w-36">
                                    <span class="font-serif text-[10px] uppercase tracking-[0.18em] text-[#E8CFA6]">Jane Austen</span>
                                    <span class="font-serif text-xl leading-tight">PRIDE AND<br>PREJUDICE</span>
                                    <span class="text-[9px] uppercase tracking-[0.2em] text-[#E8CFA6]">A classic, still</span>
                                </article>
                                <article class="bookworm-cover-drift bookworm-cover-drift-slow flex h-56 w-36 flex-col justify-between rounded-r-md border-l-4 border-[#E6D9D0]/70 bg-gradient-to-br from-[#4e6b39] via-[#6f9450] to-[#2f4426] p-3 text-center text-[#FFF8EF] shadow-xl sm:h-64 sm:w-40">
                                    <span class="font-serif text-[10px] uppercase tracking-[0.18em] text-[#EAF2D8]">J. R. R. Tolkien</span>
                                    <span class="font-serif text-2xl leading-tight">THE<br>HOBBIT</span>
                                    <span class="text-[9px] uppercase tracking-[0.2em] text-[#EAF2D8]">There and back again</span>
                                </article>
                                <article class="bookworm-cover-drift bookworm-cover-drift-slower flex h-44 w-28 flex-col justify-between rounded-r-md border-l-4 border-[#E6D9D0]/70 bg-gradient-to-br from-[#54382A] via-[#72513A] to-[#3B2418] p-3 text-center text-[#FFF8EF] shadow-xl sm:h-52 sm:w-32">
                                    <span class="font-serif text-[10px] uppercase tracking-[0.16em] text-[#E8CFA6]">L. M. Alcott</span>
                                    <span class="font-serif text-lg leading-tight">LITTLE<br>WOMEN</span>
                                    <span class="text-[9px] uppercase tracking-[0.2em] text-[#E8CFA6]">Since 1868</span>
                                </article>
                            </div>
                            <div class="mx-auto h-3 w-full rounded-sm bg-gradient-to-b from-[#a77650] via-[#7a4f33] to-[#4e301f] shadow-lg"></div>
                            <div class="relative mt-6 flex h-20 items-end justify-center">
                                <div class="absolute inset-x-[-2rem] bottom-0 h-16 rounded-t-[100%] bg-[#7c9a5a] dark:bg-[#33452a]"></div>
                                <x-logo variant="icon" size="fill" class="relative -mb-1 h-20 w-20 drop-shadow-lg" />
                            </div>
                        </div>
                        <blockquote class="absolute -bottom-8 -left-4 max-w-[15rem] -rotate-2 rounded-2xl border border-warmborder bg-paper px-5 py-4 shadow-bookplate dark:border-[#4B3035] dark:bg-raised sm:-left-10">
                            <p class="font-hand text-2xl leading-7 text-burgundy dark:text-burgundy-dark">“A book is a door you can open again.”</p>
                            <cite class="mt-1 block text-right text-xs not-italic text-tan dark:text-[#c7a491]">— your reading journal</cite>
                        </blockquote>
                    </div>
                </div>
            </section>

            {{-- How it works --}}
            <section id="how-it-works" class="scroll-mt-20 border-y border-warmborder bg-paper/70 py-20 dark:border-[#4B3035] dark:bg-raised/60">
                <div class="mx-auto max-w-7xl px-5 sm:px-8 lg:px-12">
                    <div class="max-w-2xl">
                        <p class="eyebrow">A reader’s kind of social</p>
                        <h2 class="font-display mt-3 text-4xl text-ink dark:text-[#F4E9E2] sm:text-5xl font-bold">Make room for the books that matter.</h2>
                    </div>
                    <div class="mt-12 grid gap-5 md:grid-cols-3">
                        @foreach ([
                            ['01', 'Grow your tree', 'Shelve a book and watch a new branch sprout. Drag books between branches and name each shelf your way.'],
                            ['02', 'Open any book', 'Tap a book on your tree to open it: rate it, jot a private note, and move your bookmark as you read.'],
                            ['03', 'Read with company', 'Follow readers whose reviews make you pause, and keep the passages you want to revisit.'],
                        ] as [$num, $heading, $copy])
                            <article class="bw-card p-7 transition duration-300 hover:-translate-y-1">
                                <p class="font-display text-5xl text-burgundy/80 dark:text-burgundy-dark font-bold">{{ $num }}</p>
                                <h3 class="mt-4 font-serif text-2xl font-semibold text-ink dark:text-[#F4E9E2]">{{ $heading }}</h3>
                                <p class="mt-2 text-sm leading-6 text-walnut dark:text-[#d4c2b9]">{{ $copy }}</p>
                            </article>
                        @endforeach
                    </div>
                </div>
            </section>

            <section class="bg-parchment/70 py-14 dark:bg-[#211317]">
                <div class="mx-auto flex max-w-7xl flex-col gap-5 px-5 sm:px-8 md:flex-row md:items-center md:justify-between lg:px-12">
                    <p class="font-display text-3xl text-ink dark:text-[#F4E9E2] font-bold">“The right book finds you at the right time.”</p>
                    <p class="font-hand text-3xl text-burgundy dark:text-burgundy-dark">— the Bookworm readers</p>
                </div>
            </section>

            <section class="mx-auto max-w-7xl px-5 py-20 sm:px-8 lg:px-12">
                <div class="relative flex flex-col items-start justify-between gap-6 overflow-hidden rounded-[2rem] bg-wine-header p-8 text-[#FFF8EF] shadow-bookplate sm:flex-row sm:items-center sm:p-12">
                    <div class="pointer-events-none absolute -right-10 -top-10 h-48 w-48 rounded-full bg-white/10 blur-2xl" aria-hidden="true"></div>
                    <x-logo variant="image" class="relative w-28 shrink-0 rotate-3 sm:w-32" />
                    <div class="relative sm:mr-auto">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#F1D9C4]/80">A little home for stories</p>
                        <h2 class="font-display mt-3 text-4xl font-bold">Free to read. Free to remember.</h2>
                        <p class="mt-2 text-sm text-[#F1D9C4]">Make an account and plant your first book today.</p>
                    </div>
                    <a href="{{ route('register') }}" class="relative inline-flex shrink-0 rounded-full bg-[#FFF8EF] px-7 py-3.5 text-sm font-semibold text-burgundy-deep shadow-lg transition hover:-translate-y-0.5">Join Bookworm</a>
                </div>
            </section>
        </main>

        <x-site-footer />
    </div>
</body>
</html>
