<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#672638">
    <title>Bookworm — a little thought for today</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=caveat:500,600,700|figtree:400,500,600,700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-[#e7d8ca] font-sans text-[#392a2b] antialiased">
    <div class="relative isolate flex min-h-screen flex-col overflow-hidden bg-[radial-gradient(ellipse_at_6%_8%,rgba(220,177,112,0.45),transparent_32%),radial-gradient(ellipse_at_94%_14%,rgba(168,79,87,0.38),transparent_32%),radial-gradient(ellipse_at_88%_94%,rgba(103,38,56,0.3),transparent_34%),linear-gradient(135deg,#f0e4d2_0%,#e4d2c4_52%,#d6bdb5_100%)]">
        <div class="pointer-events-none absolute inset-0 -z-10 opacity-[0.09] [background-image:linear-gradient(rgba(103,38,56,0.22)_1px,transparent_1px),linear-gradient(90deg,rgba(103,38,56,0.22)_1px,transparent_1px)] [background-size:58px_58px]"></div>
        <div class="pointer-events-none absolute -left-40 -top-44 -z-10 h-[34rem] w-[34rem] rounded-full border border-[#8a5142]/25"></div>
        <div class="pointer-events-none absolute -right-44 -bottom-48 -z-10 h-[38rem] w-[38rem] rounded-full border border-[#672638]/20"></div>

        <header class="relative z-50 overflow-visible border-b border-[#401b27]/45 bg-[radial-gradient(ellipse_at_18%_0%,rgba(204,154,104,0.2),transparent_38%),linear-gradient(105deg,#542331_0%,#672638_52%,#713345_100%)] text-[#fff8ef] shadow-[0_16px_40px_-22px_rgba(55,24,35,0.85)]">
            <div class="pointer-events-none absolute inset-0 opacity-[0.08] [background-image:radial-gradient(rgba(255,239,216,0.9)_0.7px,transparent_0.7px)] [background-size:13px_13px]"></div>
            <div class="relative mx-auto flex max-w-7xl flex-wrap items-center justify-between gap-x-4 gap-y-4 px-4 py-4 sm:flex-nowrap sm:gap-4 sm:px-8 sm:py-8 lg:px-12">
                <a href="{{ route('home') }}" aria-label="Bookworm home" class="group flex w-full min-w-0 items-center gap-3 rounded-xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#e6c795] sm:w-auto sm:gap-4">
                    <svg class="h-12 w-12 shrink-0 transition-transform duration-300 group-hover:-rotate-3 group-hover:scale-105 sm:h-20 sm:w-20" viewBox="0 0 140 140" fill="none" aria-hidden="true">
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
                        <span class="mt-1.5 block h-px w-7 bg-[#e3bd82]/80 sm:mt-2" aria-hidden="true"></span>
                        <span class="mt-1 block max-w-[18rem] font-serif text-[11px] italic leading-snug tracking-wide text-[#f0dbca]/90 sm:mt-1.5 sm:max-w-[20rem] sm:text-xs">
                            Find your next favorite book, and share the stories you love.
                        </span>
                    </span>
                </a>

                @auth
                    <nav class="flex w-full items-center justify-between gap-2 sm:w-auto sm:shrink-0 sm:justify-start sm:gap-4" aria-label="Main navigation">
                        <a href="{{ route('books.index') }}" class="rounded-full border border-[#f1d9c4]/20 px-3 py-2 text-xs font-semibold text-[#fff8ef] transition hover:border-[#f1d9c4]/45 hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#e6c795] sm:px-5 sm:text-sm">Books</a>
                        <a href="{{ route('users.index') }}" class="rounded-full border border-[#f1d9c4]/20 px-3 py-2 text-xs font-semibold text-[#fff8ef] transition hover:border-[#f1d9c4]/45 hover:bg-white/10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#e6c795] sm:px-5 sm:text-sm">Readers</a>
                        <details class="group relative">
                                <summary class="flex cursor-pointer list-none items-center gap-2 rounded-full border border-[#f1d9c4]/35 bg-white/[0.08] py-2 pl-2 pr-3.5 text-sm font-medium text-[#fff8ef] transition hover:bg-white/[0.14] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#e6c795] [&::-webkit-details-marker]:hidden">
                                    @if (Auth::user()->profile_photo_url)
                                        <img src="{{ Auth::user()->profile_photo_url }}" alt="" class="h-9 w-9 rounded-full object-cover">
                                    @else
                                        <span class="flex h-9 w-9 items-center justify-center rounded-full bg-[#e3bd82] font-semibold text-[#542331]" aria-hidden="true">{{ strtoupper(substr(Auth::user()->name, 0, 1)) }}</span>
                                    @endif
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
                @else
                    <nav class="flex w-full items-center justify-end gap-2 sm:w-auto sm:shrink-0 sm:gap-3" aria-label="Main navigation">
                        <a href="{{ route('login') }}" class="rounded-full px-2 py-2 text-xs font-medium text-[#fff8ef] transition hover:bg-white/10 sm:px-4 sm:text-sm">Log in</a>
                        <a href="{{ route('register') }}" class="rounded-full bg-[#e3bd82] px-3 py-2.5 text-xs font-semibold text-[#542331] shadow-sm transition hover:bg-[#edce9f] sm:px-5 sm:text-sm"><span class="sm:hidden">Join</span><span class="hidden sm:inline">Join Bookworm</span></a>
                    </nav>
                @endauth
            </div>
            <div class="pointer-events-none absolute inset-x-0 bottom-0 h-[3px] bg-gradient-to-r from-transparent via-[#e3bd82]/90 to-transparent"></div>
        </header>

        <main class="relative z-0 mx-auto flex w-full max-w-6xl flex-1 flex-col items-center justify-center px-5 py-7 sm:px-8 sm:py-9 lg:px-12">
            <div class="mb-6 w-full max-w-3xl text-center sm:mb-8">
                <h1 class="font-handwritten text-5xl font-bold leading-tight text-[#672638] sm:text-6xl">Welcome, {{ Auth::user()->name }}!</h1>
            </div>
            <article class="relative w-full max-w-3xl overflow-hidden rounded-[2rem] border border-[#e3cdbb] bg-[linear-gradient(145deg,#fffaf4_0%,#fbf3eb_54%,#f5e9e1_100%)] px-6 py-9 shadow-[0_24px_70px_-38px_rgba(91,51,48,0.38)] sm:rounded-[2.25rem] sm:px-10 sm:py-11 lg:px-14 lg:py-12" aria-labelledby="daily-quote-title">
                <div class="pointer-events-none absolute inset-[9px] rounded-[1.55rem] border border-[#e9d8c8]/80 sm:inset-3 sm:rounded-[1.8rem]"></div>
                <div class="pointer-events-none absolute -right-16 -top-20 h-56 w-56 rounded-full bg-[#d9b9a8]/15 blur-2xl"></div>
                <div class="pointer-events-none absolute -right-10 -top-12 h-44 w-44 rounded-full border border-[#c99d82]/20"></div>
                <div class="pointer-events-none absolute -right-2 -top-4 h-32 w-32 rounded-full border border-[#c99d82]/15"></div>
                <div class="pointer-events-none absolute left-0 top-10 bottom-10 w-1 rounded-r-full bg-gradient-to-b from-transparent via-[#ad7765]/45 to-transparent"></div>
                <div class="pointer-events-none absolute -bottom-8 -left-8 h-36 w-36 rounded-full bg-[#d9b9a8]/15 blur-2xl"></div>

                <div class="relative mx-auto max-w-lg text-center">
                    <div class="mb-6 flex flex-col items-center gap-2">
                        <h2 id="daily-quote-title" class="font-serif text-xl font-medium tracking-wide text-[#694a3c] sm:text-2xl">A little thought for today</h2>
                        <p class="text-[10px] font-medium uppercase tracking-[0.16em] text-[#a18470]">{{ now()->format('l, F j') }}</p>
                    </div>
                    <svg class="mx-auto mb-3 h-7 w-7 text-[#ae7b69]/65" viewBox="0 0 40 40" fill="currentColor" aria-hidden="true"><path d="M9 11h10v10c0 6-3 10-9 12l-2-4c3-1 5-3 5-6H9V11Zm15 0h10v10c0 6-3 10-9 12l-2-4c3-1 5-3 5-6h-4V11Z"/></svg>

                    @if ($quote)
                        <blockquote class="font-serif text-xl leading-[1.7] text-[#59453e] sm:text-2xl sm:leading-[1.75]">
                            “{{ $quote['text'] }}”
                        </blockquote>
                        <div class="mx-auto mt-6 flex items-center justify-center gap-2" aria-hidden="true">
                            <span class="h-px w-8 bg-[#c9a991]/60"></span>
                            <span class="h-1.5 w-1.5 rotate-45 bg-[#ad7765]/70"></span>
                            <span class="h-px w-8 bg-[#c9a991]/60"></span>
                        </div>
                        <p class="mt-3 font-handwritten text-2xl font-semibold text-[#7a3948] sm:text-3xl">— {{ $quote['author'] }}</p>
                    @elseif ($quoteError)
                        <p class="font-serif text-lg italic leading-relaxed text-[#806a5e] sm:text-xl">Today’s quote couldn’t be loaded just now. Please come back in a little while.</p>
                    @endif

                    <footer class="mt-8 flex flex-col items-center gap-2 border-t border-[#e5d4c7] pt-5">
                        <p class="text-[10px] text-[#8a756a]">
                            Inspirational quotes provided by
                            <a href="https://zenquotes.io/" target="_blank" rel="noopener noreferrer" class="font-semibold text-[#713345] underline decoration-[#c09a83]/60 underline-offset-2 hover:text-[#542331]">ZenQuotes API</a>
                        </p>
                    </footer>
                </div>
            </article>
        </main>
    </div>
</body>
</html>
