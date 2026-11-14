<x-app-layout>
    <x-slot name="title">Quote wall · Bookworm</x-slot>
    <x-slot name="header">
        <x-page-header eyebrow="Lines worth keeping" title="The quote wall" subtitle="Every passage readers have shared. Like the ones that land, and pass them on.">
            <x-slot name="actions">
                <a href="{{ route('feed.index') }}" class="btn-primary">❝ Share a quote</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="page-section space-y-8">
        <div class="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <nav class="flex gap-2" aria-label="Quote filter">
                @foreach (['everyone' => 'Everyone', 'mine' => 'My quotes'] as $key => $label)
                    <a href="{{ route('quotes.index', array_filter(['scope' => $key === 'mine' ? 'mine' : null, 'q' => $search ?: null])) }}" @if ($scope === $key) aria-current="page" @endif
                       class="{{ $scope === $key ? 'border-burgundy bg-burgundy text-[#FFF8EF] dark:border-burgundy-dark dark:bg-burgundy-dark' : 'border-warmborder text-walnut hover:border-burgundy/40 dark:border-[#4B3035] dark:text-[#e3d3ca]' }} focus-ring rounded-full border px-4 py-2 text-sm font-semibold transition">{{ $label }}</a>
                @endforeach
            </nav>
            <form method="GET" action="{{ route('quotes.index') }}" class="flex w-full max-w-sm gap-2" role="search">
                @if ($scope === 'mine')<input type="hidden" name="scope" value="mine">@endif
                <label for="quote-search" class="sr-only">Search quotes</label>
                <input id="quote-search" type="search" name="q" value="{{ $search }}" placeholder="Search a word, book or author" class="bw-input min-w-0 flex-1 text-sm">
                <button class="btn-ghost min-h-0 py-2">Search</button>
            </form>
        </div>

        @if ($unposted->isNotEmpty())
            <section class="bw-card p-5 sm:p-6" aria-labelledby="unposted-heading">
                <p class="eyebrow">From your PDF highlights</p>
                <h2 id="unposted-heading" class="mt-1 font-serif text-2xl font-semibold text-ink dark:text-[#F4E9E2]">Saved, not shared yet</h2>
                <div class="mt-4 flex snap-x gap-4 overflow-x-auto pb-2">
                    @foreach ($unposted as $card)
                        <form method="POST" action="{{ route('posts.store') }}" class="flex w-72 shrink-0 snap-start flex-col rounded-2xl border border-warmborder bg-parchment/50 p-4 dark:border-[#4B3035] dark:bg-white/5">
                            @csrf
                            <input type="hidden" name="type" value="quote">
                            <input type="hidden" name="quote_card_id" value="{{ $card->id }}">
                            <p class="line-clamp-4 flex-1 font-display text-2xl font-bold leading-snug text-ink dark:text-[#F4E9E2]">“{{ $card->highlight->text }}”</p>
                            <p class="mt-2 truncate text-xs text-tan">{{ $card->highlight->bookPdf->book->title ?? '' }} · p. {{ $card->highlight->page_number }}</p>
                            <button class="btn-primary mt-3 min-h-0 py-2 text-xs">Share to the feed</button>
                        </form>
                    @endforeach
                </div>
            </section>
        @endif

        @if ($quotes->isEmpty())
            <x-empty-state :title="$search ? 'No quotes match' : 'No quotes yet'" :message="$search ? 'Try another word, book or author.' : 'Share the first one from the feed — type a line or pick one of your highlights.'" />
        @else
            <div class="columns-1 gap-5 sm:columns-2 lg:columns-3 [&>*]:mb-5">
                @foreach ($quotes as $quote)
                    <article id="post-{{ $quote->id }}" class="bw-card break-inside-avoid overflow-hidden">
                        <figure class="relative bg-gradient-to-br from-[#4A1220] via-[#6D1A2B] to-[#8B2A3D] px-6 pb-6 pt-8 text-[#FFF8EF]">
                            <span class="pointer-events-none absolute -left-1 -top-5 font-display text-[8rem] leading-none text-white/10" aria-hidden="true">“</span>
                            <blockquote class="relative font-display text-[1.7rem] font-bold leading-snug">{{ $quote->quote_text }}</blockquote>
                            <figcaption class="relative mt-4 text-sm text-[#F1D9C4]">— {{ $quote->book?->title ?? 'Unknown book' }}@if ($quote->book?->author_name), {{ $quote->book->author_name }}@endif @if ($quote->quote_page)· p. {{ $quote->quote_page }}@endif</figcaption>
                        </figure>
                        <div class="flex items-center gap-2 px-4 py-3">
                            <x-avatar :src="$quote->user->profile_photo_url" :name="$quote->user->name" size="sm" />
                            <p class="min-w-0 flex-1 truncate text-xs text-tan">Kept by <a href="{{ route('users.show', $quote->user->username) }}" class="font-semibold text-burgundy hover:underline dark:text-[#E9A2AE]">{{ $quote->user->name }}</a></p>
                            <button type="button" class="focus-ring rounded-full px-2 py-1 text-xs font-semibold text-tan underline hover:text-burgundy" data-download-quote data-quote="{{ $quote->quote_text }}" data-title="{{ $quote->book?->title ?? '' }}" data-author="{{ $quote->book?->author_name ?? '' }}" data-reader="{{ $quote->user->name }}" data-page="{{ $quote->quote_page ?? '' }}">Card</button>
                        </div>
                        @if ($quote->body)
                            <p class="px-4 pb-3 text-sm leading-6 text-walnut dark:text-[#d4c2b9]">{{ $quote->body }}</p>
                        @endif
                        @include('feed.partials.reactions', ['item' => $quote])
                    </article>
                @endforeach
            </div>
            <div>{{ $quotes->links() }}</div>
        @endif
    </div>
</x-app-layout>
