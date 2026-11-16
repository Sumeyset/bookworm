<x-app-layout>
    <x-slot name="title">{{ $name }} · Bookworm</x-slot>

    <div class="page-section space-y-8">
        <section class="author-hero">
            <span class="author-hero__mark" aria-hidden="true">{{ mb_strtoupper(mb_substr($name, 0, 1)) }}</span>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold uppercase tracking-[0.25em] text-[#F2C38B]">Author</p>
                <h1 class="mt-1 font-display text-5xl font-bold leading-none sm:text-6xl">{{ $name }}</h1>
                <p class="mt-3 text-[#F1D9C4]">{{ $books->count() }} {{ Str::plural('book', $books->count()) }} on Bookworm · read by {{ $readers }} {{ Str::plural('reader', $readers) }}</p>
            </div>
            <button type="button" class="rounded-full bg-[#FFF8EF] px-5 py-2.5 text-sm font-semibold text-burgundy-deep" data-ask-barnaby="Tell me about the author {{ $name }}: who they are, what their books are like, and which one I should start with, spoiler-free.">
                Ask Barnaby about {{ Str::before($name, ' ') ?: $name }}
            </button>
        </section>

        <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_20rem]">
            <section aria-labelledby="author-books">
                <h2 id="author-books" class="mb-4 font-display text-3xl font-bold text-ink dark:text-[#f4e9e2]">Books</h2>
                <div class="grid grid-cols-2 gap-5 sm:grid-cols-3 md:grid-cols-4">
                    @foreach ($books as $book)
                        @php($r = $ratings->get($book->id))
                        <a href="{{ route('books.show', $book) }}" class="group">
                            <x-book-cover :book="$book" class="w-full transition duration-300 group-hover:-translate-y-1" />
                            <span class="mt-2 line-clamp-2 block font-serif font-semibold leading-5 text-ink group-hover:text-burgundy dark:text-[#F4E9E2]">{{ $book->title }}</span>
                            <span class="block text-xs text-tan">
                                {{ $book->first_publish_year }}
                                @if ($r) · {{ number_format($r->stars, 1) }}★ ({{ $r->votes }}) @endif
                            </span>
                            @if ($mine->has($book->id))
                                <span class="mt-1 inline-block rounded-full bg-parchment px-2 py-0.5 text-[11px] font-semibold text-walnut dark:bg-white/10 dark:text-[#e3d3ca]">{{ ['read' => '✅ Read', 'reading' => '📖 Reading', 'want_to_read' => '🔖 Want to read'][$mine[$book->id]] ?? '' }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </section>

            <aside class="space-y-6">
                <section class="bw-card p-5">
                    <h2 class="font-serif text-xl font-semibold text-ink dark:text-[#f4e9e2]">Friends who read {{ Str::afterLast($name, ' ') }}</h2>
                    @forelse ($friends as $friend)
                        <a href="{{ route('users.show', $friend['user']->username) }}" class="mt-3 flex items-center gap-3">
                            <x-avatar :src="$friend['user']->profile_photo_url" :name="$friend['user']->name" size="sm" />
                            <span class="min-w-0 flex-1 truncate text-sm font-semibold text-ink dark:text-[#F4E9E2]">{{ $friend['user']->name }}</span>
                            <span class="text-xs text-tan">{{ $friend['count'] }} {{ Str::plural('book', $friend['count']) }}</span>
                        </a>
                    @empty
                        <p class="mt-2 text-sm text-tan">None of the readers you follow have read them yet.</p>
                    @endforelse
                </section>
            </aside>
        </div>

        @if ($quotes->isNotEmpty())
            <section aria-labelledby="author-quotes">
                <h2 id="author-quotes" class="mb-4 font-display text-3xl font-bold text-ink dark:text-[#f4e9e2]">Lines readers kept</h2>
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    @foreach ($quotes as $quote)
                        <a href="{{ route('posts.show', $quote) }}" class="author-quote">
                            <blockquote>“{{ Str::limit($quote->quote_text, 220) }}”</blockquote>
                            <p class="mt-3 text-xs">— {{ $quote->book?->title }} · kept by {{ $quote->user->name }}{{ $quote->likes_count ? ' · ♥ ' . $quote->likes_count : '' }}</p>
                        </a>
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
