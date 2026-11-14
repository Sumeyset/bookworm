@php
    $firstName = Str::before($profile->name, ' ');
    $goalPercent = min(100, (int) round($recap['books'] / max(1, $recap['goal']) * 100));
    $shareUrl = route('year.show', [$profile->username, $recap['year']]);
@endphp
<x-app-layout>
    <x-slot name="title">{{ $firstName }}’s {{ $recap['year'] }} in books · Bookworm</x-slot>

    <div class="page-section space-y-8">
        {{-- Hero --}}
        <section class="relative overflow-hidden rounded-[2rem] bg-[linear-gradient(135deg,#2a0d15_0%,#4A1220_40%,#6D1A2B_75%,#8B2A3D_100%)] px-6 py-12 text-[#FFF8EF] shadow-bookplate sm:px-12 sm:py-16">
            <div class="pointer-events-none absolute -right-20 -top-24 h-80 w-80 rounded-full bg-gold/25 blur-3xl" aria-hidden="true"></div>
            <div class="pointer-events-none absolute -bottom-24 left-1/3 h-72 w-72 rounded-full bg-[#7FA35B]/15 blur-3xl" aria-hidden="true"></div>
            @if ($recap['covers']->isNotEmpty())
                <div class="pointer-events-none absolute inset-y-0 right-0 hidden w-1/2 grid-cols-4 gap-2 p-6 opacity-30 [mask-image:linear-gradient(to_left,black,transparent)] lg:grid" aria-hidden="true">
                    @foreach ($recap['covers']->take(16) as $book)
                        <img src="{{ $book->cover_src }}" alt="" class="aspect-[2/3] w-full rotate-[-4deg] rounded-md object-cover" loading="lazy">
                    @endforeach
                </div>
            @endif
            <div class="relative max-w-2xl">
                <p class="text-xs font-semibold uppercase tracking-[0.3em] text-[#F2C38B]">Year in reading</p>
                <h1 class="mt-3 font-display text-6xl font-bold leading-[0.95] sm:text-8xl">{{ $firstName }}’s {{ $recap['year'] }}<br>in books</h1>
                <p class="mt-6 text-lg text-[#F1D9C4]">
                    @if ($recap['books'])
                        {{ $recap['books'] }} {{ Str::plural('book', $recap['books']) }} finished, {{ number_format($recap['pages']) }} pages turned, and {{ $recap['reading_days'] }} {{ Str::plural('day', $recap['reading_days']) }} spent reading.
                    @else
                        The pages are still waiting. Finish a book this year and your recap will fill in here.
                    @endif
                </p>
                <div class="mt-8 flex flex-wrap gap-2">
                    @if ($isOwner && ! $profile->is_public)
                        <form method="POST" action="{{ route('share.toggle') }}">@csrf<button class="rounded-full bg-[#FFF8EF] px-5 py-2.5 text-sm font-semibold text-burgundy-deep">Turn on sharing</button></form>
                    @else
                        <button type="button" data-copy-link="{{ $shareUrl }}" class="rounded-full bg-[#FFF8EF] px-5 py-2.5 text-sm font-semibold text-burgundy-deep transition hover:-translate-y-0.5">Copy link</button>
                    @endif
                    <button type="button" data-recap-card
                            data-name="{{ $profile->name }}" data-year="{{ $recap['year'] }}" data-books="{{ $recap['books'] }}" data-pages="{{ number_format($recap['pages']) }}"
                            data-days="{{ $recap['reading_days'] }}" data-streak="{{ $recap['longest_streak'] }}"
                            data-top="{{ $recap['top_rated']->first()?->book->title ?? '' }}"
                            class="rounded-full bg-white/15 px-5 py-2.5 text-sm font-semibold ring-1 ring-inset ring-white/25 transition hover:bg-white/25">Download recap card</button>
                    @if ($recap['year'] > 2000)
                        <a href="{{ route('year.show', [$profile->username, $recap['year'] - 1]) }}" class="rounded-full px-4 py-2.5 text-sm font-semibold text-[#F1D9C4] hover:underline">&larr; {{ $recap['year'] - 1 }}</a>
                    @endif
                </div>
            </div>
        </section>

        {{-- Stat tiles --}}
        <div class="grid grid-cols-2 gap-4 lg:grid-cols-4">
            @foreach ([
                ['Books finished', $recap['books'], $recap['goal'] ? $goalPercent . '% of a ' . $recap['goal'] . '-book goal' : null],
                ['Pages read', number_format($recap['pages']), $recap['books'] ? 'about ' . number_format(intdiv($recap['pages'], max(1, $recap['books']))) . ' per book' : null],
                ['Reading days', $recap['reading_days'], $recap['longest_streak'] ? 'longest streak: ' . $recap['longest_streak'] . ' ' . Str::plural('day', $recap['longest_streak']) : null],
                ['Average rating', $recap['average_rating'] ?: '—', $recap['busiest_month'] ? 'busiest month: ' . $recap['busiest_month'] : null],
            ] as [$label, $value, $hint])
                <div class="bw-card p-5">
                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-tan">{{ $label }}</p>
                    <p class="mt-2 font-display text-6xl font-bold leading-none text-ink dark:text-[#F4E9E2]">{{ $value }}</p>
                    @if ($hint)<p class="mt-2 text-sm text-walnut dark:text-[#d4c2b9]">{{ $hint }}</p>@endif
                </div>
            @endforeach
        </div>

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_minmax(0,1fr)]">
            {{-- Month by month --}}
            <section class="bw-card p-6">
                <p class="eyebrow">Month by month</p>
                <div class="mt-5 grid h-44 grid-cols-12 items-end gap-1.5" aria-label="Books finished each month">
                    @php $maxMonth = max(1, $recap['months']->max()); @endphp
                    @foreach ($recap['months'] as $i => $count)
                        <div class="flex h-full flex-col items-center justify-end gap-1.5">
                            @if ($count)<span class="text-[10px] font-semibold text-burgundy dark:text-[#E9A2AE]">{{ $count }}</span>@endif
                            <span class="w-full rounded-t-lg {{ $count ? 'bg-gradient-to-t from-burgundy to-[#c25670]' : 'bg-warmborder dark:bg-white/10' }}" style="height: {{ $count ? max(8, $count / $maxMonth * 100) : 3 }}%"></span>
                            <span class="text-[10px] text-tan">{{ \Carbon\Carbon::create()->month($i + 1)->format('M') }}</span>
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- Top rated --}}
            <section class="bw-card p-6">
                <p class="eyebrow">Highest rated</p>
                @if ($recap['top_rated']->isEmpty())
                    <p class="mt-4 text-sm text-tan">Rate the books you finish and your favourites will appear here.</p>
                @else
                    <ol class="mt-4 space-y-3">
                        @foreach ($recap['top_rated'] as $i => $item)
                            <li class="flex items-center gap-3">
                                <span class="w-6 font-display text-3xl font-bold text-gold">{{ $i + 1 }}</span>
                                <x-book-cover :book="$item->book" class="w-10" />
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate font-serif font-semibold text-ink dark:text-[#F4E9E2]">{{ $item->book->title }}</span>
                                    <span class="block truncate text-xs text-tan">{{ $item->book->author_name }}</span>
                                </span>
                                <x-star-display :rating="$item->my_rating" />
                            </li>
                        @endforeach
                    </ol>
                @endif
            </section>
        </div>

        <div class="grid gap-6 md:grid-cols-3">
            @foreach ([['First book of the year', $recap['first_book']], ['Longest read', $recap['longest_book']], ['Most recent finish', $recap['last_book']]] as [$label, $item])
                <section class="bw-card flex gap-4 p-5">
                    @if ($item)
                        <x-book-cover :book="$item->book" class="w-16 shrink-0" />
                        <div class="min-w-0">
                            <p class="eyebrow">{{ $label }}</p>
                            <p class="mt-1 font-serif text-lg font-semibold leading-tight text-ink dark:text-[#F4E9E2]">{{ $item->book->title }}</p>
                            <p class="mt-1 text-xs text-tan">{{ $label === 'Longest read' && ($item->total_pages || $item->book->page_count) ? number_format($item->total_pages ?: $item->book->page_count) . ' pages' : $item->read_at?->format('M j') }}</p>
                        </div>
                    @else
                        <div><p class="eyebrow">{{ $label }}</p><p class="mt-2 text-sm text-tan">Not yet.</p></div>
                    @endif
                </section>
            @endforeach
        </div>

        @if ($recap['top_authors']->isNotEmpty() || $recap['quote'])
            <div class="grid gap-6 lg:grid-cols-2">
                @if ($recap['top_authors']->isNotEmpty())
                    <section class="bw-card p-6">
                        <p class="eyebrow">Authors you kept coming back to</p>
                        <ul class="mt-4 space-y-2">
                            @foreach ($recap['top_authors'] as $author => $count)
                                <li class="flex items-baseline justify-between gap-3 border-b border-dashed border-warmborder pb-2 dark:border-[#4B3035]">
                                    <span class="font-display text-3xl font-bold text-ink dark:text-[#F4E9E2]">{{ $author }}</span>
                                    <span class="text-sm text-tan">{{ $count }} {{ Str::plural('book', $count) }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </section>
                @endif
                @if ($recap['quote'])
                    <figure class="relative overflow-hidden rounded-3xl bg-gradient-to-br from-[#4A1220] to-[#8B2A3D] p-8 text-[#FFF8EF] shadow-bookplate">
                        <p class="text-xs font-semibold uppercase tracking-[0.2em] text-[#F2C38B]">Quote of the year</p>
                        <blockquote class="mt-4 font-display text-4xl font-bold leading-tight">“{{ $recap['quote']->quote_text }}”</blockquote>
                        <figcaption class="mt-4 text-sm text-[#F1D9C4]">— {{ $recap['quote']->book?->title }}</figcaption>
                    </figure>
                @endif
            </div>
        @endif
    </div>
</x-app-layout>
