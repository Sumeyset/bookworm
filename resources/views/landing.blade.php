<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#4A1220">
    <meta name="description" content="Bookworm gives your books a cosy room of their own. Track your reading, keep a streak, read with friends and get recommendations from Barnaby the bookworm.">
    <title>Bookworm — Your reading life, beautifully shelved</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}">
        <link rel="apple-touch-icon" href="{{ asset('apple-touch-icon.png') }}">
        <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
        <meta name="apple-mobile-web-app-capable" content="yes">
        <meta name="apple-mobile-web-app-title" content="Bookworm">
    @include('layouts.partials.head-assets')
</head>
<body id="top" class="bg-ivory font-sans text-ink antialiased dark:bg-wineblack dark:text-[#F4E9E2]">
    <div class="paper-grain min-h-screen overflow-x-hidden">
        @include('layouts.navigation')

        <main>
            @php
                // A demo room: the Window nook with made-up books on its real shelves.
                $scene = \App\Support\ShelfScene::layout('window', 0);
                $W = $scene['width'];
                $H = 1116;
                $season = \App\Support\ShelfStyle::season(now());
                $hour = (int) now()->format('G');
                $part = match (true) { $hour >= 5 && $hour < 8 => 'dawn', $hour >= 8 && $hour < 17 => 'day', $hour >= 17 && $hour < 20 => 'dusk', default => 'night' };
                $titles = [
                    ['The Lantern Orchard', '#7a2233'], ['Moss & Stars', '#4e6b39'], ['The Paper Fox', '#8a5a36'], ['Salt and Honey', '#c49a4a'],
                    ['Kite Weather', '#3f6a80'], ['A Map of Small Hours', '#5b3a5e'], ['The Quiet Lighthouse', '#2f4a5c'], ['Winter Tea', '#a8644a'],
                    ['Fern Letters', '#5f7d45'], ['The Clockmaker’s Cat', '#6b2a35'], ['Puddle Kings', '#b07a3a'], ['Thirteen Lanterns', '#3a3550'],
                    ['North of Nowhere', '#456b5c'], ['Plum Season', '#7a3a5a'], ['The Long Thaw', '#365470'], ['Ink & Ivy', '#2f5a3e'],
                    ['Small Brave Things', '#9a4b3c'], ['Starling Road', '#54606e'], ['The Honey Thief', '#b8873a'], ['Night Garden', '#26324a'],
                    ['Bread & Ballads', '#8c6a3f'], ['The Tin Comet', '#5a5a66'], ['Under the Linden', '#4f6a3a'], ['Rain on Paper', '#6a4a6e'],
                    ['Marmalade Days', '#c27a3a'], ['The Green Door', '#3e5e3a'], ['Wolf Moon Café', '#4a2a35'], ['Sea Glass', '#4f8090'],
                ];
                $spines = [];
                $t = 0;
                foreach (array_slice($scene['slots'], 0, 8) as $i => $slot) {
                    $count = [7, 6, 8, 7, 6, 8, 7, 6][$i];
                    $widths = [];
                    for ($k = 0; $k < $count; $k++) {
                        $widths[] = 30 + (($t + $k) * 7) % 14;
                    }
                    $x = $slot['align'] === 'end' ? $slot['x2'] - array_sum($widths) - 6 : $slot['x1'] + 6;
                    foreach ($widths as $k => $w) {
                        [$title, $color] = $titles[$t % count($titles)];
                        $h = $slot['h'] * (0.72 + (($t * 37) % 26) / 100);
                        $spines[] = [
                            'title' => $title, 'color' => $color,
                            'left' => $x / $W * 100, 'bottom' => ($H - $slot['y']) / $H * 100,
                            'width' => $w / $W * 100, 'height' => $h / $H * 100,
                        ];
                        $x += $w;
                        $t++;
                    }
                }
                $glass = $scene['window'];
            @endphp

            {{-- Hero --}}
            <section class="landing-hero">
                <div class="mx-auto max-w-6xl px-5 pt-14 text-center sm:px-8 sm:pt-20">
                    <p class="inline-flex items-center gap-2 rounded-full border border-gold/40 bg-paper/70 px-3 py-1.5 text-xs font-semibold uppercase tracking-[0.2em] text-tan dark:bg-white/5 dark:text-[#c7a491]">
                        <span class="h-1.5 w-1.5 rounded-full bg-[#7FA35B]"></span> A living library for readers
                    </p>
                    <h1 class="font-display mx-auto mt-6 max-w-4xl text-5xl font-bold leading-[0.95] text-ink dark:text-[#F4E9E2] sm:text-6xl lg:text-7xl">
                        Your reading life, <span class="text-burgundy dark:text-burgundy-dark">beautifully shelved.</span>
                    </h1>
                    <p class="mx-auto mt-6 max-w-2xl text-lg leading-8 text-walnut dark:text-[#d4c2b9]">
                        Every book you read finds a place in your own cosy room. Track your reading, keep your streak, read with friends, and let Barnaby the bookworm find your next favourite.
                    </p>
                    <div class="mt-8 flex flex-wrap items-center justify-center gap-3">
                        <a href="{{ route('register') }}" class="btn-primary px-7 py-3.5 text-base">Start your shelf — it’s free</a>
                        <a href="#features" class="btn-ghost px-6 py-3.5 text-base">See what’s inside <span aria-hidden="true">&darr;</span></a>
                    </div>
                    <p class="mt-4 text-sm text-tan dark:text-[#c7a491]">Already a reader? <a href="{{ route('login') }}" class="font-semibold text-burgundy underline underline-offset-4 dark:text-burgundy-dark">Log in</a></p>
                </div>

                {{-- A room you can play with --}}
                <div class="mx-auto mt-12 max-w-6xl px-3 sm:px-8"
                     x-data="{ season: '{{ $season }}', part: '{{ $part }}' }">
                    <figure class="landing-room" :class="'landing-room--' + part">
                        <div class="landing-room__scene" style="--glass-l: {{ $glass['x1'] / $W * 100 }}%; --glass-t: {{ $glass['y1'] / $H * 100 }}%; --glass-w: {{ ($glass['x2'] - $glass['x1']) / $W * 100 }}%; --glass-h: {{ ($glass['y2'] - $glass['y1']) / $H * 100 }}%;">
                            <img class="landing-room__view" src="{{ \App\Support\StaticAsset::url("images/shelves/window/{$season}-{$part}.webp") }}"
                                 :src="'{{ asset('images/shelves/window') }}/' + season + '-' + part + '.webp'" alt="" width="1200" height="1200" decoding="async">
                            <img class="landing-room__img" src="{{ \App\Support\StaticAsset::url($scene['image']) }}" alt="" width="2000" height="1116" decoding="async" fetchpriority="high">
                            <img class="landing-room__sash" src="{{ \App\Support\StaticAsset::url($scene['sash']['image']) }}" alt=""
                                 style="left: {{ $scene['sash']['left'] }}%; top: {{ $scene['sash']['top'] }}%; width: {{ $scene['sash']['width'] }}%; height: {{ $scene['sash']['height'] }}%;">
                            @foreach ($spines as $spine)
                                <span class="landing-spine" style="left: {{ round($spine['left'], 3) }}%; bottom: {{ round($spine['bottom'], 3) }}%; width: {{ round($spine['width'], 3) }}%; height: {{ round($spine['height'], 3) }}%; --spine: {{ $spine['color'] }};" title="{{ $spine['title'] }}"><span>{{ $spine['title'] }}</span></span>
                            @endforeach
                            <span class="landing-room__shade" aria-hidden="true"></span>
                        </div>
                        <figcaption class="sr-only">A sample Bookworm room: a window nook with shelves full of books on both sides.</figcaption>

                        {{-- Little glimpses of the app around the room --}}
                        <div class="landing-chip landing-chip--streak" aria-hidden="true"><span class="text-2xl">🔥</span><span><b>12-day streak</b><small>You read today</small></span></div>
                        <div class="landing-chip landing-chip--reading" aria-hidden="true">
                            <span class="landing-chip__cover" style="--spine: #4e6b39;"></span>
                            <span><b>Moss &amp; Stars</b><small>Page 214 · 64%</small><i class="landing-chip__bar"><i style="width: 64%"></i></i></span>
                        </div>
                        <div class="landing-chip landing-chip--barnaby" aria-hidden="true">
                            <x-barnaby-avatar class="h-10 w-10 shrink-0" />
                            <span><b>Barnaby</b><small>Loved Moss &amp; Stars? Try <em>The Paper Fox</em> next.</small></span>
                        </div>
                    </figure>

                    <div class="landing-controls" role="group" aria-label="Try the window">
                        <span class="landing-controls__label">Try the window:</span>
                        @foreach (['spring' => '🌸 Spring', 'summer' => '☀️ Summer', 'autumn' => '🍂 Autumn', 'winter' => '❄️ Winter'] as $key => $label)
                            <button type="button" class="landing-pill" :class="season === '{{ $key }}' && 'is-on'" :aria-pressed="season === '{{ $key }}'" @click="season = '{{ $key }}'">{{ $label }}</button>
                        @endforeach
                        <span class="landing-controls__sep" aria-hidden="true"></span>
                        @foreach (['dawn' => 'Dawn', 'day' => 'Day', 'dusk' => 'Dusk', 'night' => 'Night'] as $key => $label)
                            <button type="button" class="landing-pill" :class="part === '{{ $key }}' && 'is-on'" :aria-pressed="part === '{{ $key }}'" @click="part = '{{ $key }}'">{{ $label }}</button>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- Features --}}
            <section id="features" class="scroll-mt-24 mx-auto max-w-6xl px-5 py-20 sm:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="eyebrow">Everything a reader needs</p>
                    <h2 class="font-display mt-3 text-4xl font-bold text-ink dark:text-[#F4E9E2] sm:text-5xl">More than a list of books.</h2>
                </div>
                <div class="mt-12 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ([
                        ['🪴', 'A room that grows', 'Your books stand on real shelves: a living tree, a window nook, wall shelves or a Christmas tree. Decorate it with things you earn by reading.', '#e9f0dd'],
                        ['📖', 'Read and track anything', 'Open your own PDFs and EPUBs in a calm reader, or log paper books and audiobooks by page or minute. Your bookmark follows you.', '#f6e6d4'],
                        ['🔥', 'Streaks & challenges', 'Keep a gentle daily streak, take on monthly challenges and collect badges and acorns along the way.', '#f8e1d6'],
                        ['🐛', 'Barnaby, your book buddy', 'Ask Barnaby what to read next, who a character was, or where you left off. He knows your shelf.', '#f3dde3'],
                        ['👥', 'Read together', 'Book clubs, buddy reads and live reading rooms with a shared timer. Leave sticky notes on a friend’s books.', '#e1e9f1'],
                        ['✨', 'Your year in books', 'A beautiful recap of everything you read, ready to share at the end of the year.', '#f6efd6'],
                    ] as [$emoji, $title, $copy, $tint])
                        <article class="bw-card landing-feature p-6" style="--tint: {{ $tint }};">
                            <span class="landing-feature__icon" aria-hidden="true">{{ $emoji }}</span>
                            <h3 class="mt-4 font-serif text-2xl font-semibold text-ink dark:text-[#F4E9E2]">{{ $title }}</h3>
                            <p class="mt-2 text-sm leading-6 text-walnut dark:text-[#d4c2b9]">{{ $copy }}</p>
                        </article>
                    @endforeach
                </div>
            </section>

            {{-- Rooms --}}
            <section class="border-y border-warmborder bg-paper/70 py-20 dark:border-[#4B3035] dark:bg-raised/60">
                <div class="mx-auto max-w-6xl px-5 sm:px-8">
                    <div class="flex flex-wrap items-end justify-between gap-4">
                        <div class="max-w-xl">
                            <p class="eyebrow">Pick your room</p>
                            <h2 class="font-display mt-3 text-4xl font-bold text-ink dark:text-[#F4E9E2] sm:text-5xl">Four places to keep your books.</h2>
                        </div>
                        <p class="max-w-sm text-sm leading-6 text-walnut dark:text-[#d4c2b9]">Switch any time. Your books move with you, and the spines take their colour from each book’s cover.</p>
                    </div>
                    <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
                        @foreach ([
                            'tree' => [\App\Support\TreeBackdrop::IMAGE, '52% 35%'],
                            'window' => ['images/shelves/window-nook.webp', '50% 40%'],
                            'wall' => ['images/shelves/wall-shelf.webp', '50% 50%'],
                            'christmas' => ['images/shelves/christmas-tree-shelf.webp', '50% 45%'],
                        ] as $key => [$image, $position])
                            <figure class="landing-style">
                                <img src="{{ \App\Support\StaticAsset::url($image) }}" alt="" loading="lazy" decoding="async" style="object-position: {{ $position }};@if ($key === 'window') background: url('{{ \App\Support\StaticAsset::url('images/shelves/window/summer-day.webp') }}') 50% 40% / 46% 88% no-repeat, #9fc0dc;@endif">
                                <figcaption>
                                    <b>{{ \App\Support\ShelfStyle::STYLES[$key][0] }}</b>
                                    <span>{{ \App\Support\ShelfStyle::STYLES[$key][1] }}</span>
                                </figcaption>
                            </figure>
                        @endforeach
                    </div>
                </div>
            </section>

            {{-- How it works --}}
            <section class="mx-auto max-w-6xl px-5 py-20 sm:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <p class="eyebrow">Three small steps</p>
                    <h2 class="font-display mt-3 text-4xl font-bold text-ink dark:text-[#F4E9E2] sm:text-5xl">Start in a minute.</h2>
                </div>
                <ol class="landing-steps mt-12">
                    @foreach ([
                        ['Shelve your books', 'Search any book, or bring your list from another reading app with a CSV file.'],
                        ['Read a little each day', 'Move your bookmark, log minutes for audiobooks, and watch your streak and your room grow.'],
                        ['Share what you love', 'Follow friends, post reviews and quotes, join a club, or read side by side in a live room.'],
                    ] as $i => [$title, $copy])
                        <li class="bw-card p-6">
                            <span class="landing-steps__num">{{ $i + 1 }}</span>
                            <h3 class="mt-4 font-serif text-2xl font-semibold text-ink dark:text-[#F4E9E2]">{{ $title }}</h3>
                            <p class="mt-2 text-sm leading-6 text-walnut dark:text-[#d4c2b9]">{{ $copy }}</p>
                        </li>
                    @endforeach
                </ol>
            </section>

            {{-- Questions --}}
            <section class="mx-auto max-w-3xl px-5 pb-6 sm:px-8">
                <h2 class="font-display text-center text-4xl font-bold text-ink dark:text-[#F4E9E2]">Questions</h2>
                <div class="mt-8 space-y-3">
                    @foreach ([
                        ['Is Bookworm free?', 'Yes. Your shelf, the reader, streaks, clubs and Barnaby are all free. An optional Pro plan adds a few extras for readers who want more.'],
                        ['Can I bring my books from another app?', 'Yes. Export your library as a CSV file from your old reading app and import it. Ratings, dates and shelves come along.'],
                        ['Who can see my shelf?', 'You decide. Make your account private so only approved followers see it, hide your activity, or share a public link to your room.'],
                        ['Can I read my own ebooks?', 'Upload PDF or EPUB files you own and read them in Bookworm. Your files stay private to you.'],
                    ] as [$q, $a])
                        <details class="landing-faq bw-card">
                            <summary>{{ $q }}</summary>
                            <p>{{ $a }}</p>
                        </details>
                    @endforeach
                </div>
            </section>

            {{-- Call to action --}}
            <section class="mx-auto max-w-6xl px-5 py-16 sm:px-8">
                <div class="relative flex flex-col items-start justify-between gap-6 overflow-hidden rounded-[2rem] bg-wine-header p-8 text-[#FFF8EF] shadow-bookplate sm:flex-row sm:items-center sm:p-12">
                    <div class="pointer-events-none absolute -right-10 -top-10 h-48 w-48 rounded-full bg-white/10 blur-2xl" aria-hidden="true"></div>
                    <x-logo variant="image" class="relative w-24 shrink-0 rotate-3 sm:w-28" />
                    <div class="relative sm:mr-auto">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#F1D9C4]/80">A little home for stories</p>
                        <h2 class="font-display mt-3 text-4xl font-bold">Your first shelf is waiting.</h2>
                        <p class="mt-2 text-sm text-[#F1D9C4]">Make an account and place your first book today.</p>
                    </div>
                    <a href="{{ route('register') }}" class="relative inline-flex shrink-0 rounded-full bg-[#FFF8EF] px-7 py-3.5 text-sm font-semibold text-burgundy-deep shadow-lg transition hover:-translate-y-0.5">Join Bookworm</a>
                </div>
            </section>
        </main>

        <x-site-footer />
    </div>
</body>
</html>
