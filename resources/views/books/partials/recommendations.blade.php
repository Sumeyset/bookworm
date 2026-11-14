{{-- Rows of recommended books with a one-click "Want to read" --}}
@foreach ($recommendations as $section)
    <section aria-label="{{ $section['title'] }}" class="space-y-4">
        <div>
            <h2 class="font-display text-4xl font-bold leading-none text-ink dark:text-[#F4E9E2]">{{ $section['title'] }}</h2>
            <p class="mt-1 text-sm text-tan">{{ $section['subtitle'] }}</p>
        </div>
        <div class="-mx-4 flex snap-x gap-4 overflow-x-auto px-4 pb-3 sm:mx-0 sm:px-0">
            @foreach ($section['books'] as $book)
                <article class="group w-36 shrink-0 snap-start sm:w-40">
                    <a href="{{ route('books.show', $book) }}" class="block transition duration-300 group-hover:-translate-y-1"><x-book-cover :book="$book" class="w-full" /></a>
                    <a href="{{ route('books.show', $book) }}" class="mt-2 line-clamp-2 font-serif text-base font-semibold leading-5 text-ink hover:text-burgundy dark:text-[#F4E9E2]">{{ $book->title }}</a>
                    <p class="truncate text-xs text-tan">{{ $book->author_name ?? 'Unknown author' }}</p>
                    <form method="POST" action="{{ route('books.shelf.update', $book) }}" class="mt-2">
                        @csrf
                        <input type="hidden" name="status" value="want_to_read">
                        <button class="bw-chip w-full justify-center py-1.5 text-xs">+ Want to read</button>
                    </form>
                </article>
            @endforeach
        </div>
    </section>
@endforeach
