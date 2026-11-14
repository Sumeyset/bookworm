<x-app-layout>
    <x-slot name="title">{{ $query !== '' ? '“' . $query . '” · ' : '' }}Search books · Bookworm</x-slot>
    <x-slot name="header">
        <x-page-header eyebrow="Discover" title="Search books" subtitle="Find your next favorite book, and share the stories you love." />
    </x-slot>

    @php
        $genres = auth()->user()->preferred_genres ?: ['Classics', 'Fantasy', 'Mystery', 'Science fiction', 'Romance', 'Poetry', 'History'];
    @endphp

    <div class="page-section space-y-8">
        <div class="space-y-4">
            <form method="GET" action="{{ route('books.index') }}" class="bw-search" role="search">
                <label for="book-search" class="sr-only">Search by title or author</label>
                <svg class="bw-search__icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.5 3a5.5 5.5 0 1 0 3.46 9.78l3.63 3.63a.75.75 0 1 0 1.06-1.06l-3.63-3.63A5.5 5.5 0 0 0 8.5 3ZM4.5 8.5a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd"/></svg>
                <input id="book-search" name="q" type="search" value="{{ $query }}" placeholder="Search by title or author" autocomplete="off" class="bw-search__input" @if ($query === '') autofocus @endif>
                <button type="submit" class="btn-primary shrink-0 sm:px-7">Search</button>
            </form>
            <div class="flex flex-wrap items-center gap-2">
                <span class="mr-1 text-xs font-semibold uppercase tracking-[0.18em] text-tan">{{ auth()->user()->preferred_genres ? 'Explore a favorite kind of story' : 'Try' }}</span>
                @foreach ($genres as $genre)
                    <a href="{{ route('books.index', ['q' => $genre]) }}" class="bw-chip">{{ $genre }}</a>
                @endforeach
            </div>
        </div>

        @if ($query !== '')
            @if ($error)
                <x-empty-state title="The library is a little slow" :message="$error" />
            @elseif (count($results) === 0)
                <x-empty-state title="No books found" :message="'Nothing matched “' . $query . '”. Try a shorter title or the author’s last name.'" />
            @else
                <div>
                    <p class="mb-5 text-sm text-tan">{{ count($results) }} {{ Str::plural('result', count($results)) }} for <span class="font-semibold text-ink dark:text-[#F4E9E2]">“{{ $query }}”</span></p>
                    <div class="grid grid-cols-2 gap-x-5 gap-y-8 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5">
                        @foreach ($results as $book)
                            <article class="group flex flex-col">
                                <a href="{{ route('books.show', $book['database_id']) }}" class="relative block overflow-hidden rounded-2xl bg-parchment shadow-[0_18px_30px_-18px_rgba(42,26,29,0.65)] ring-1 ring-black/5 transition duration-300 group-hover:-translate-y-1.5 group-hover:shadow-[0_28px_40px_-20px_rgba(42,26,29,0.7)] dark:bg-[#382329]" tabindex="-1" aria-hidden="true">
                                    @if ($book['cover_url'])
                                        <img src="{{ $book['database_id'] ? route('covers.show', $book['database_id']) : $book['cover_url'] }}" alt="" loading="lazy" decoding="async" width="180" height="270" class="aspect-[2/3] w-full object-cover">
                                    @else
                                        <span class="flex aspect-[2/3] items-center justify-center bg-gradient-to-br from-burgundy via-[#8B2A3D] to-burgundy-deep p-4 text-center font-serif text-lg font-semibold leading-snug text-[#FFF8EF]">{{ $book['title'] }}</span>
                                    @endif
                                    <span class="pointer-events-none absolute inset-y-0 left-0 w-3 bg-gradient-to-r from-black/30 to-transparent"></span>
                                    @if ($book['first_publish_year'])
                                        <span class="absolute left-2 top-2 rounded-full bg-paper/90 px-2.5 py-1 text-[10px] font-semibold tracking-wide text-walnut shadow-sm backdrop-blur dark:bg-[#241a1b]/90 dark:text-[#ddc9be]">{{ $book['first_publish_year'] }}</span>
                                    @endif
                                </a>
                                <div class="mt-3 flex-1">
                                    <a href="{{ route('books.show', $book['database_id']) }}" class="focus-ring line-clamp-2 font-serif text-lg font-semibold leading-6 text-ink hover:text-burgundy dark:text-[#f4e9e2] dark:hover:text-[#E9A2AE]">{{ $book['title'] }}</a>
                                    <p class="mt-1 line-clamp-1 text-sm text-tan dark:text-[#c1aaa0]">{{ $book['author_name'] ?? 'Unknown author' }}</p>
                                </div>
                                <form method="POST" action="{{ route('books.rate') }}" class="mt-2">
                                    @csrf
                                    <input type="hidden" name="ol_id" value="{{ $book['ol_id'] }}">
                                    <input type="hidden" name="title" value="{{ $book['title'] }}">
                                    <input type="hidden" name="author_name" value="{{ $book['author_name'] }}">
                                    <input type="hidden" name="cover_url" value="{{ $book['cover_url'] }}">
                                    <input type="hidden" name="first_publish_year" value="{{ $book['first_publish_year'] }}">
                                    <x-star-input :value="$book['rating']" submit-on-select compact />
                                </form>
                                <a href="{{ route('books.show', $book['database_id']) }}" class="btn-ghost mt-3 min-h-0 py-2 text-xs">Book details &amp; reviews</a>
                            </article>
                        @endforeach
                    </div>
                </div>
            @endif
        @else
            @include('books.partials.recommendations')
            <div class="grid gap-5 md:grid-cols-3">
                @foreach ([
                    ['Search', 'Type a title or author above to start.', 'M8.5 3a5.5 5.5 0 1 0 3.46 9.78l3.63 3.63a.75.75 0 1 0 1.06-1.06l-3.63-3.63A5.5 5.5 0 0 0 8.5 3Z'],
                    ['Shelve it', 'Open a book and add it to your tree — it lands on a branch straight away.', 'M4 16V4a2 2 0 0 1 2-2h9v14H6a2 2 0 0 0-2 2Zm0 0a2 2 0 0 0 2 2h9'],
                    ['Rate & remember', 'Tap the stars on any result to rate it in one click.', 'M10 2l2.4 5 5.6.6-4.2 3.8 1.2 5.6L10 14.3 5 17l1.2-5.6L2 7.6 7.6 7 10 2Z'],
                ] as [$heading, $copy, $icon])
                    <div class="bw-card p-6">
                        <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-burgundy/10 text-burgundy dark:bg-white/10 dark:text-[#E9A2AE]">
                            <svg class="h-5 w-5" viewBox="0 0 20 20" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linejoin="round" aria-hidden="true"><path d="{{ $icon }}"/></svg>
                        </span>
                        <h2 class="mt-4 font-serif text-2xl font-semibold text-ink dark:text-[#F4E9E2]">{{ $heading }}</h2>
                        <p class="mt-1 text-sm leading-6 text-walnut dark:text-[#d4c2b9]">{{ $copy }}</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
