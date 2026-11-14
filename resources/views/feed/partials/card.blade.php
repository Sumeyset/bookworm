{{-- One feed item: a post, a shared quote, or a review. --}}
@php
    $isReview = $item instanceof \App\Models\Review;
    $isQuote = ! $isReview && $item->isQuote();
    $verb = $isReview ? ($item->body ? 'reviewed' : 'rated') : ($isQuote ? 'shared a quote' : 'posted');
@endphp
<article id="{{ $item->getMorphClass() }}-{{ $item->id }}" class="bw-card scroll-mt-28 overflow-hidden">
    <header class="flex items-center gap-3 px-5 pt-4">
        <a href="{{ route('users.show', $item->user->username) }}" class="shrink-0"><x-avatar :src="$item->user->profile_photo_url" :name="$item->user->name" size="sm" /></a>
        <p class="min-w-0 flex-1 truncate text-sm text-tan dark:text-[#c7a491]">
            <a href="{{ route('users.show', $item->user->username) }}" class="focus-ring rounded-sm font-semibold text-ink hover:text-burgundy dark:text-[#F4E9E2] dark:hover:text-[#E9A2AE]">{{ $item->user->name }}</a>
            {{ $verb }}
        </p>
        <time datetime="{{ $item->created_at->toIso8601String() }}" class="shrink-0 text-xs text-tan">{{ $item->created_at->diffForHumans(short: true) }}</time>
        @if (! $isReview && $item->user_id === auth()->id())
            <form method="POST" action="{{ route('posts.destroy', $item) }}" onsubmit="return confirm('Delete this post?')">
                @csrf @method('DELETE')
                <button class="focus-ring rounded-full p-1 text-tan hover:bg-parchment hover:text-burgundy dark:hover:bg-white/5" aria-label="Delete post">
                    <svg class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M7 3h6l1 2h3v2H3V5h3l1-2Zm-2 6h10l-1 8H6L5 9Z"/></svg>
                </button>
            </form>
        @endif
    </header>

    <div class="px-5 pb-4 pt-3">
        @if ($isQuote)
            <figure class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-[#4A1220] via-[#6D1A2B] to-[#8B2A3D] px-6 py-7 text-[#FFF8EF] shadow-inner">
                <span class="pointer-events-none absolute -left-1 -top-6 font-display text-[9rem] leading-none text-white/10" aria-hidden="true">“</span>
                <blockquote class="relative font-display text-3xl font-bold leading-snug">{{ $item->quote_text }}</blockquote>
                <figcaption class="relative mt-4 text-sm text-[#F1D9C4]">
                    — {{ $item->book?->title ?? 'Unknown book' }}@if ($item->book?->author_name), {{ $item->book->author_name }}@endif @if ($item->quote_page)· p. {{ $item->quote_page }}@endif
                </figcaption>
            </figure>
            @if ($item->body)
                <p class="mt-3 whitespace-pre-line break-words text-[15px] leading-7 text-ink dark:text-[#F4E9E2]">{{ $item->body }}</p>
            @endif
        @elseif ($isReview)
            <div class="flex gap-4">
                <a href="{{ route('books.show', $item->book) }}" class="shrink-0" tabindex="-1" aria-hidden="true"><x-book-cover :book="$item->book" class="w-20 sm:w-24" /></a>
                <div class="min-w-0">
                    <h2 class="font-serif text-2xl font-semibold leading-tight text-ink dark:text-[#F4E9E2]"><a href="{{ route('books.show', $item->book) }}" class="hover:text-burgundy dark:hover:text-[#E9A2AE]">{{ $item->book->title }}</a></h2>
                    <p class="mt-1 text-sm text-tan">{{ $item->book->author_name ?? 'Unknown author' }}</p>
                    <div class="mt-2"><x-star-display :rating="$item->rating" /></div>
                    @if ($item->body)
                        <blockquote class="mt-3 border-l-2 border-gold/60 pl-4 font-serif text-lg italic leading-8 text-walnut dark:text-[#e3d3ca]">{{ $item->body }}</blockquote>
                    @endif
                </div>
            </div>
        @else
            <p class="whitespace-pre-line break-words text-[17px] leading-8 text-ink dark:text-[#F4E9E2]">{{ $item->body }}</p>
            @if ($item->book)
                <a href="{{ route('books.show', $item->book) }}" class="mt-4 flex items-center gap-3 rounded-2xl border border-warmborder bg-parchment/50 p-3 transition hover:border-burgundy/30 dark:border-[#4B3035] dark:bg-white/5">
                    <x-book-cover :book="$item->book" class="w-12" />
                    <span class="min-w-0">
                        <span class="block truncate font-serif text-lg font-semibold text-ink dark:text-[#F4E9E2]">{{ $item->book->title }}</span>
                        <span class="block truncate text-xs text-tan">{{ $item->book->author_name ?? 'Unknown author' }}</span>
                    </span>
                </a>
            @endif
        @endif
    </div>

    @include('feed.partials.reactions', ['item' => $item])
</article>
