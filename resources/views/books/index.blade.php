<x-app-layout>
    <x-slot name="header">
        <h2 class="font-serif text-2xl font-semibold leading-tight text-ink dark:text-[#F4E9E2]">
            Search books
        </h2>
        <p class="mt-1 text-sm text-tan dark:text-[#c7a491]">Find your next favorite book, and share the stories you love.</p>
    </x-slot>

    <div class="py-8">
        <div class="mx-auto max-w-5xl px-4">
            <form method="GET" action="{{ route('books.index') }}" class="flex gap-2">
                <x-text-input name="q" type="text" class="flex-1"
                              placeholder="Search by title or author" :value="$query" />
                <button type="submit" class="focus-ring inline-flex min-h-11 items-center gap-2 rounded-full bg-burgundy px-5 py-2.5 text-sm font-semibold text-[#fff8ef] shadow-sm transition hover:-translate-y-0.5 hover:bg-burgundy-hover">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd" d="M8.5 3a5.5 5.5 0 1 0 3.455 9.78l3.632 3.633a.75.75 0 1 0 1.06-1.061l-3.632-3.632A5.5 5.5 0 0 0 8.5 3ZM4.5 8.5a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd" />
                    </svg>
                    Search
                </button>
            </form>

            @if (session('status'))
                <div class="mt-4 rounded-xl border border-green-200 bg-green-50 p-3 text-sm text-green-800" role="status">
                    {{ session('status') }}
                </div>
            @endif

            @if ($query !== '')
                @if ($error)
                    <p class="mt-6 text-red-600 dark:text-red-400" role="alert">{{ $error }}</p>
                @elseif (count($results) === 0)
                    <p class="mt-6 text-gray-600 dark:text-gray-400">No books found for "{{ $query }}".</p>
                @else
                    <div class="mt-6 grid grid-cols-2 gap-4 sm:grid-cols-3 sm:gap-5 md:grid-cols-4 lg:grid-cols-5">
                        @foreach ($results as $book)
                            <article class="bookplate group flex flex-col overflow-hidden rounded-xl transition duration-200 hover:-translate-y-1 hover:border-[#d6baa0] hover:shadow-lg">
                                <div class="relative overflow-hidden bg-[#eee4d8] dark:bg-[#382c2d]">
                                    @if ($book['cover_url'])
                                        <img src="{{ $book['cover_url'] }}" alt="Cover of {{ $book['title'] }}"
                                             loading="lazy" class="aspect-[2/3] w-full object-cover transition duration-300 group-hover:scale-[1.03]">
                                    @else
                                        <div class="flex aspect-[2/3] items-center justify-center bg-parchment p-4 text-center dark:bg-[#382329]">
                                            <div>
                                                <svg class="mx-auto h-8 w-8 text-[#9b7466] dark:text-[#c7a491]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 4.75A2.75 2.75 0 0 1 7.75 2H20v17H7.75A2.75 2.75 0 0 0 5 21.75v-17Z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 18.5A2.5 2.5 0 0 1 7.5 16H20" />
                                                </svg>
                                                <p class="mt-3 line-clamp-4 font-serif text-lg font-semibold text-walnut dark:text-[#ddc9be]">{{ $book['title'] }}</p>
                                            </div>
                                        </div>
                                    @endif
                                    @if ($book['first_publish_year'])
                                        <span class="absolute left-2 top-2 rounded-full bg-[#fffaf4]/95 px-2.5 py-1 text-[10px] font-semibold tracking-wide text-[#72584d] shadow-sm dark:bg-[#241a1b]/90 dark:text-[#ddc9be]">
                                            {{ $book['first_publish_year'] }}
                                        </span>
                                    @endif
                                </div>

                                <div class="flex-1 px-3.5 pb-3 pt-3.5">
                                    <a href="{{ route('books.show', $book['database_id']) }}"
                                       class="focus-ring line-clamp-2 min-h-10 font-serif text-base font-semibold leading-5 text-ink hover:text-burgundy dark:text-[#f4e9e2] dark:hover:text-burgundy-dark">
                                        {{ $book['title'] }}
                                    </a>
                                    <p class="mt-1.5 line-clamp-1 text-xs text-[#8a7065] dark:text-[#c1aaa0]">
                                        {{ $book['author_name'] ?? 'Unknown author' }}
                                    </p>
                                </div>

                                <a href="{{ route('books.show', $book['database_id']) }}"
                                   class="focus-ring mx-3.5 mb-3 inline-flex items-center justify-center gap-1.5 rounded-full border border-burgundy/25 bg-paper px-3 py-2 text-xs font-semibold text-burgundy transition hover:border-burgundy/50 hover:bg-rosetint dark:border-[#594749] dark:bg-[#302526] dark:text-[#f0d8c1]">
                                    Book details &amp; reviews
                                </a>

                                <form method="POST" action="{{ route('books.rate') }}"
                                      class="border-t border-[#f0e8df] bg-[#faf5ef] px-2 py-2.5 dark:border-[#453538] dark:bg-[#241b1c]">
                                    @csrf
                                    <input type="hidden" name="ol_id" value="{{ $book['ol_id'] }}">
                                    <input type="hidden" name="title" value="{{ $book['title'] }}">
                                    <input type="hidden" name="author_name" value="{{ $book['author_name'] }}">
                                    <input type="hidden" name="cover_url" value="{{ $book['cover_url'] }}">
                                    <input type="hidden" name="first_publish_year" value="{{ $book['first_publish_year'] }}">
                                    <x-star-input :value="$book['rating']" submit-on-select compact />
                                </form>
                            </article>
                        @endforeach
                    </div>
                @endif
            @else
                @if (auth()->user()->preferred_genres)
                    <section class="mt-8" aria-labelledby="genre-discovery-heading">
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-tan dark:text-[#c7a491]">A starting point, picked for you</p>
                        <h2 id="genre-discovery-heading" class="mt-1 font-serif text-xl font-semibold text-ink dark:text-[#F4E9E2]">Explore a favorite kind of story</h2>
                        <div class="mt-4 flex flex-wrap gap-2">
                            @foreach (auth()->user()->preferred_genres as $genre)
                                <a href="{{ route('books.index', ['q' => $genre]) }}" class="focus-ring rounded-full border border-burgundy/25 bg-paper px-4 py-2 text-sm font-semibold text-burgundy transition hover:bg-rosetint dark:border-[#594749] dark:bg-[#302526] dark:text-burgundy-dark">{{ $genre }} <span aria-hidden="true">&rarr;</span></a>
                            @endforeach
                        </div>
                    </section>
                @else
                    <p class="mt-6 text-gray-600 dark:text-gray-400">Type a title or author above to start.</p>
                @endif
            @endif
        </div>
    </div>
</x-app-layout>
