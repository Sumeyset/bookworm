<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#b99083">
    <title>Bookworm — a little home for stories</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=caveat:500,600,700|figtree:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#d9c2b4] font-sans text-[#392a2b] antialiased">
    <div class="relative isolate flex min-h-screen flex-col overflow-hidden bg-[radial-gradient(ellipse_at_8%_8%,rgba(250,224,184,0.9),transparent_32%),radial-gradient(ellipse_at_91%_16%,rgba(174,76,85,0.48),transparent_30%),radial-gradient(ellipse_at_86%_88%,rgba(103,38,56,0.34),transparent_32%),radial-gradient(ellipse_at_15%_84%,rgba(194,139,61,0.4),transparent_30%),radial-gradient(ellipse_at_52%_0%,rgba(208,137,116,0.28),transparent_44%),linear-gradient(135deg,#ead9c6_0%,#d9c2b4_48%,#cfb4aa_100%)]">
        <div class="pointer-events-none absolute inset-0 z-0 opacity-[0.1] [background-image:linear-gradient(rgba(103,38,56,0.2)_1px,transparent_1px),linear-gradient(90deg,rgba(103,38,56,0.2)_1px,transparent_1px)] [background-size:56px_56px]"></div>
        <div class="pointer-events-none absolute -left-44 -top-48 z-0 h-[38rem] w-[38rem] rounded-full border border-[#8a5142]/35"></div>
        <div class="pointer-events-none absolute -left-32 -top-36 z-0 h-[31rem] w-[31rem] rounded-full border border-[#8a5142]/25"></div>
        <div class="pointer-events-none absolute -bottom-52 -right-40 z-0 h-[38rem] w-[38rem] rounded-full border border-[#672638]/28"></div>
        <div class="pointer-events-none absolute -right-24 top-[16%] z-0 h-80 w-80 rounded-full bg-[#bd7166]/35 blur-3xl"></div>
        <div class="pointer-events-none absolute -bottom-20 left-[18%] z-0 h-80 w-80 rounded-full bg-[#c39a5b]/30 blur-3xl"></div>

        <header class="relative z-10 mx-auto flex w-full max-w-7xl items-center justify-end gap-2 px-6 py-6 sm:px-10">
            @auth
                <a href="{{ route('books.index') }}" class="rounded-full px-4 py-2 text-sm font-medium text-[#76564e] transition hover:bg-white/60 hover:text-[#672638]">Explore books</a>
                <a href="{{ route('dashboard') }}" class="rounded-full bg-[#672638] px-5 py-2.5 text-sm font-semibold text-[#fffaf4] transition hover:bg-[#7b3545]">My bookshelf</a>
            @else
                <a href="{{ route('login') }}" class="rounded-full px-4 py-2 text-sm font-medium text-[#76564e] transition hover:bg-white/60 hover:text-[#672638]">Log in</a>
                <a href="{{ route('register') }}" class="rounded-full bg-[#672638] px-5 py-2.5 text-sm font-semibold text-[#fffaf4] transition hover:bg-[#7b3545]">Join Bookworm</a>
            @endauth
        </header>

        <main class="relative z-10 mx-auto flex w-full max-w-7xl flex-1 items-center px-5 pb-10 pt-2 sm:px-8 sm:pb-14 lg:px-12">
            <div class="relative mx-auto grid w-full max-w-6xl items-center gap-8 overflow-hidden rounded-[2rem] border border-[#b9957e]/70 bg-[#f8f2e9]/90 px-6 py-12 shadow-[0_32px_100px_-42px_rgba(76,42,39,0.46)] sm:gap-10 sm:rounded-[2.5rem] sm:px-12 sm:py-16 lg:grid-cols-[0.9fr_1.1fr] lg:gap-4 lg:px-16 lg:py-20">
                <div class="pointer-events-none absolute inset-[9px] rounded-[1.5rem] border border-[#d8c5b2]/60 sm:inset-3 sm:rounded-[2rem]"></div>
                <div class="pointer-events-none absolute -right-20 -top-24 h-72 w-72 rounded-full border border-[#cbb29d]/30"></div>
                <div class="pointer-events-none absolute -right-12 -top-16 h-56 w-56 rounded-full border border-[#cbb29d]/30"></div>
                <div class="pointer-events-none absolute -bottom-32 -left-20 h-64 w-64 rounded-full bg-[#8f4c4b]/[0.04] blur-2xl"></div>
                <div class="pointer-events-none absolute bottom-0 left-0 top-0 w-1.5 bg-gradient-to-b from-[#b28a68] via-[#672638] to-[#8a5142]"></div>

                <section class="relative z-10 flex flex-col items-center text-center lg:items-start lg:pl-2 lg:text-left" aria-labelledby="welcome-title">
                    <div class="mb-5 flex items-center gap-3 text-[10px] font-semibold uppercase tracking-[0.3em] text-[#9b7865] sm:text-xs">
                        <span class="h-px w-8 bg-[#a77863]"></span>
                        For the love of stories
                        <span class="h-px w-8 bg-[#a77863] lg:hidden"></span>
                    </div>
                    <a href="{{ route('home') }}" aria-label="Bookworm home" class="group relative mb-4 inline-flex rounded-full outline-none transition-transform duration-500 ease-out hover:scale-110 focus-visible:ring-4 focus-visible:ring-[#9b655e]/40">
                        <span class="absolute -inset-3 rounded-full border border-[#b78d76]/40"></span>
                        <span class="absolute -inset-6 rounded-full border border-[#b78d76]/20"></span>
                        <svg class="relative h-28 w-28 drop-shadow-[0_9px_9px_rgba(79,42,38,0.18)] transition-transform duration-500 group-hover:-rotate-3 sm:h-32 sm:w-32" viewBox="0 0 140 140" fill="none" aria-hidden="true">
                            <path d="M18 55c15-8 33-7 50 3v58c-17-9-35-10-50-3V55Z" fill="#672638"/>
                            <path d="M122 55c-15-8-33-7-50 3v58c17-9 35-10 50-3V55Z" fill="#87434A"/>
                            <path d="M24 61c12-5 25-3 38 4v40c-13-6-26-8-38-4V61Z" fill="#F5EBDD"/>
                            <path d="M116 61c-12-5-25-3-38 4v40c13-6 26-8 38-4V61Z" fill="#EAD8C7"/>
                            <path d="M70 57v59" stroke="#B58771" stroke-width="3.5" stroke-linecap="round"/>
                            <path d="M70 59c-8-7-7-15-2-20 5-4 12-7 11-14" stroke="#A96B4F" stroke-width="10" stroke-linecap="round"/>
                            <circle cx="79" cy="19" r="11" fill="#B77A58"/>
                            <circle cx="75.5" cy="16.5" r="1.7" fill="#392A2B"/>
                            <circle cx="82.5" cy="16.5" r="1.7" fill="#392A2B"/>
                            <path d="M76.5 22c1.5 1.5 4.5 1.5 6 0" stroke="#392A2B" stroke-width="1.5" stroke-linecap="round"/>
                            <path d="M31 68v29m78-29v29" stroke="#B58771" stroke-opacity=".55" stroke-width="1.5"/>
                            <path d="M39 90h14m34 0h14" stroke="#C8A88D" stroke-width="1.5" stroke-linecap="round"/>
                        </svg>
                    </a>
                    <h1 id="welcome-title" class="font-handwritten text-7xl font-bold leading-[0.82] tracking-wide text-[#672638] [text-shadow:0_2px_0_rgba(255,255,255,0.75)] sm:text-8xl lg:text-[6.7rem]">
                        Bookworm
                    </h1>
                    <span class="mt-7 h-px w-16 bg-[#a77863]"></span>
                    <p class="mt-5 max-w-sm font-serif text-lg italic leading-8 text-[#76564e] sm:text-xl">
                        Find your next favorite book, and share the stories you love.
                    </p>
                    <div class="mt-7 flex flex-wrap items-center justify-center gap-3 lg:justify-start">
                        @auth
                            <a href="{{ route('books.index') }}" class="inline-flex items-center gap-2 rounded-full bg-[#672638] px-6 py-3 text-sm font-semibold text-[#fffaf4] shadow-[0_10px_24px_-12px_rgba(103,38,56,0.75)] transition duration-200 hover:-translate-y-0.5 hover:bg-[#7b3545] focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[#9b655e]/40">
                                Explore books
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69L10.22 5.03a.75.75 0 1 1 1.06-1.06l5.5 5.5a.75.75 0 0 1 0 1.06l-5.5 5.5a.75.75 0 1 1-1.06-1.06l4.22-4.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                            </a>
                        @else
                            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 rounded-full bg-[#672638] px-6 py-3 text-sm font-semibold text-[#fffaf4] shadow-[0_10px_24px_-12px_rgba(103,38,56,0.75)] transition duration-200 hover:-translate-y-0.5 hover:bg-[#7b3545] focus-visible:outline-none focus-visible:ring-4 focus-visible:ring-[#9b655e]/40">
                                Join to explore books
                                <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M3 10a.75.75 0 0 1 .75-.75h10.69L10.22 5.03a.75.75 0 1 1 1.06-1.06l5.5 5.5a.75.75 0 0 1 0 1.06l-5.5 5.5a.75.75 0 1 1-1.06-1.06l4.22-4.22H3.75A.75.75 0 0 1 3 10Z" clip-rule="evenodd"/></svg>
                            </a>
                        @endauth
                        <div class="flex items-center gap-2 text-xs font-medium tracking-wide text-[#9b7865]" aria-hidden="true">
                            <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor"><path d="M10 1.5 12 8l6.5 2-6.5 2-2 6.5L8 12l-6.5-2L8 8l2-6.5Z"/></svg>
                            Your next favorite is waiting
                        </div>
                    </div>
                </section>

                <div class="relative mx-auto flex h-[17rem] w-full max-w-[37rem] items-center justify-center sm:h-[25rem] lg:h-[29rem] max-[480px]:gap-1" role="img" aria-label="Three illustrated books: Pride and Prejudice by Jane Austen, The Hobbit by J. R. R. Tolkien, and Little Women by Louisa May Alcott">
                    <div class="absolute left-1/2 top-1/2 h-[70%] w-[76%] -translate-x-1/2 -translate-y-1/2 rounded-full bg-[radial-gradient(ellipse,rgba(219,177,105,0.3),rgba(171,103,83,0.12)_48%,transparent_72%)]"></div>
                    <div class="absolute left-1/2 top-1/2 h-[82%] w-[88%] -translate-x-1/2 -translate-y-1/2 rounded-full border border-dashed border-[#a77863]/25"></div>
                    <svg class="absolute right-[10%] top-[5%] h-10 w-10 animate-pulse text-[#b28a4b]/80 motion-reduce:animate-none" viewBox="0 0 40 40" fill="currentColor" aria-hidden="true"><path d="m20 2 3.6 13.4L37 19l-13.4 3.6L20 36l-3.6-13.4L20 2Z"/></svg>
                    <span class="absolute left-[7%] top-[18%] h-2.5 w-2.5 rounded-full bg-[#b87b62] shadow-[0_0_0_5px_rgba(184,123,98,0.12)]"></span>
                    <span class="absolute bottom-[19%] right-[8%] h-2 w-2 rounded-full bg-[#c5a45f] shadow-[0_0_0_5px_rgba(197,164,95,0.14)]"></span>
                    <span class="absolute bottom-[7%] left-[18%] hidden -rotate-6 rounded-full border border-[#a77863]/30 bg-[#fffaf3]/75 px-3 py-1 font-serif text-[9px] italic tracking-[0.12em] text-[#8a5142] sm:block">stories worth keeping</span>
                    <div class="absolute bottom-[8%] left-[4%] right-[4%] h-28 rounded-[50%] bg-[radial-gradient(ellipse,rgba(103,38,56,0.12),transparent_68%)]"></div>
                    <div class="absolute bottom-[10%] left-1/2 h-10 w-[85%] -translate-x-1/2 rounded-[50%] bg-[#58392f]/20 blur-xl"></div>
                    <div class="absolute bottom-[13%] left-[10%] right-[10%] h-3 rounded-full bg-[#a88b73]/25 blur-md"></div>

                    <div class="bookworm-cover-drift bookworm-cover-drift-slow absolute left-[5%] top-[23%] z-[1] h-52 w-32 -rotate-[14deg] rounded-r-md rounded-l-sm bg-gradient-to-br from-[#824255] via-[#713345] to-[#542638] shadow-[12px_24px_28px_-17px_rgba(57,42,43,0.65)] transition duration-300 hover:-rotate-[10deg] hover:shadow-[16px_28px_32px_-15px_rgba(57,42,43,0.55)] sm:left-[8%] sm:h-64 sm:w-40 max-[480px]:relative max-[480px]:left-auto max-[480px]:top-auto max-[480px]:h-36 max-[480px]:w-20 max-[480px]:rotate-0">
                        <div class="absolute -right-2 inset-y-2 w-2 rounded-r-sm bg-gradient-to-r from-[#d9c6ab] to-[#a58b70]"></div>
                        <div class="absolute inset-y-0 left-3 w-1 rounded-l bg-[#c89c7c]/45"></div>
                        <div class="absolute inset-[10px] rounded-r border border-[#e4c6a6]/65"></div>
                        <div class="absolute inset-x-5 top-7 flex items-center justify-center text-[#efd8b8]">
                            <svg class="h-8 w-8" viewBox="0 0 40 40" fill="none" aria-hidden="true"><path d="M20 34V7m0 18C9 25 7 18 7 12c8 0 13 5 13 13Zm0-7c0-8 5-13 13-13 0 8-5 13-13 13Z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <div class="absolute inset-x-4 top-1/2 -translate-y-1 text-center text-[#f0d8b7]">
                            <p class="font-serif text-xs tracking-[0.1em] sm:text-sm">PRIDE AND</p>
                            <p class="font-serif text-xs tracking-[0.1em] sm:text-sm">PREJUDICE</p>
                            <span class="mx-auto mt-2 block h-px w-8 bg-current/70"></span>
                            <p class="mt-2 font-serif text-[7px] tracking-[0.08em] sm:text-[8px]">JANE AUSTEN</p>
                        </div>
                        <div class="absolute inset-x-0 bottom-7 text-center font-handwritten text-sm text-[#eacba8]">1813</div>
                    </div>

                    <div class="bookworm-cover-drift absolute left-1/2 top-[8%] z-10 h-60 w-36 -translate-x-1/2 rotate-[1deg] rounded-r-md rounded-l-sm bg-gradient-to-br from-[#a76550] via-[#8a5142] to-[#683b35] shadow-[15px_26px_32px_-17px_rgba(57,42,43,0.7)] transition duration-300 hover:rotate-0 hover:shadow-[18px_32px_38px_-16px_rgba(57,42,43,0.6)] sm:h-72 sm:w-44 max-[480px]:relative max-[480px]:left-auto max-[480px]:top-auto max-[480px]:h-40 max-[480px]:w-24 max-[480px]:translate-x-0 max-[480px]:rotate-0">
                        <div class="absolute -top-3 right-7 z-20 h-10 w-5 bg-[#d5ad5d] shadow-sm [clip-path:polygon(0_0,100%_0,100%_100%,50%_76%,0_100%)]"></div>
                        <div class="absolute -right-2 inset-y-2 w-2 rounded-r-sm bg-gradient-to-r from-[#e0ceb2] to-[#ad9275]"></div>
                        <div class="absolute inset-y-0 left-3 w-1 rounded-l bg-[#e0bd99]/50"></div>
                        <div class="absolute inset-[10px] rounded-r border border-[#eed4ad]/70"></div>
                        <div class="absolute inset-x-5 top-7 flex justify-center text-[#f1dcbb]">
                            <svg class="h-10 w-10" viewBox="0 0 48 48" fill="none" aria-hidden="true"><path d="M24 8c-5 7-6 13-1 19 1-5 4-8 8-11 1 7 6 10 5 16-1 7-7 11-14 10-8-1-13-7-12-14 .5-6 5-10 8-13 0 5 1 8 4 10 1-7 0-12 2-17Z" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round"/></svg>
                        </div>
                        <div class="absolute inset-x-4 top-[43%] text-center text-[#f5dfbd]">
                            <p class="font-serif text-xl tracking-[0.12em] sm:text-2xl">THE</p>
                            <p class="mt-1 font-serif text-lg tracking-[0.08em] sm:text-xl">HOBBIT</p>
                            <span class="mx-auto mt-3 block h-px w-10 bg-current/70"></span>
                            <p class="mt-3 font-serif text-[7px] tracking-[0.08em] text-[#ecd0a9] sm:text-[8px]">J. R. R. TOLKIEN</p>
                        </div>
                        <div class="absolute inset-x-0 bottom-8 text-center font-handwritten text-base text-[#f1d9b4]">1937</div>
                    </div>

                    <div class="bookworm-cover-drift bookworm-cover-drift-slower absolute right-[4%] top-[25%] z-[2] h-48 w-32 rotate-[13deg] rounded-r-md rounded-l-sm bg-gradient-to-br from-[#614743] via-[#493536] to-[#342729] shadow-[12px_24px_28px_-17px_rgba(57,42,43,0.65)] transition duration-300 hover:rotate-[9deg] hover:shadow-[16px_28px_32px_-15px_rgba(57,42,43,0.55)] sm:right-[7%] sm:h-60 sm:w-40 max-[480px]:relative max-[480px]:right-auto max-[480px]:top-auto max-[480px]:h-36 max-[480px]:w-20 max-[480px]:rotate-0">
                        <div class="absolute -right-2 inset-y-2 w-2 rounded-r-sm bg-gradient-to-r from-[#d1bea2] to-[#947d67]"></div>
                        <div class="absolute inset-y-0 left-3 w-1 rounded-l bg-[#bfa17e]/45"></div>
                        <div class="absolute inset-[10px] rounded-r border border-[#d9bc98]/60"></div>
                        <div class="absolute inset-x-5 top-7 flex justify-center text-[#dfc5a0]">
                            <svg class="h-8 w-8" viewBox="0 0 40 40" fill="none" aria-hidden="true"><path d="m20 4 3.5 11.5L35 19l-11.5 3.5L20 34l-3.5-11.5L5 19l11.5-3.5L20 4Z" stroke="currentColor" stroke-width="1.3" stroke-linejoin="round"/><path d="m32 5 1.5 4.5L38 11l-4.5 1.5L32 17l-1.5-4.5L26 11l4.5-1.5L32 5Z" fill="currentColor"/></svg>
                        </div>
                        <div class="absolute inset-x-4 top-1/2 -translate-y-1 text-center text-[#ead3b1]">
                            <p class="font-serif text-sm tracking-[0.13em] sm:text-base">LITTLE</p>
                            <p class="mt-1 font-serif text-sm tracking-[0.13em] sm:text-base">WOMEN</p>
                            <span class="mx-auto mt-2 block h-px w-8 bg-current/70"></span>
                            <p class="mt-2 font-serif text-[6px] tracking-[0.04em] text-[#d3b895] sm:text-[7px]">LOUISA MAY ALCOTT</p>
                        </div>
                        <div class="absolute inset-x-0 bottom-6 text-center font-handwritten text-sm text-[#d9bd99]">1868</div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
