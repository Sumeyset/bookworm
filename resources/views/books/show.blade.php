<x-app-layout>
    <x-slot name="header">
        <a href="{{ route('books.index') }}" class="focus-ring inline-flex items-center gap-2 rounded-full border border-warmborder bg-paper/70 px-4 py-2 text-sm font-semibold text-burgundy transition hover:-translate-x-0.5 hover:border-burgundy/40 dark:border-[#4B3035] dark:bg-white/5 dark:text-[#E9A2AE]">
            <span aria-hidden="true">&larr;</span> Search books
        </a>
    </x-slot>

    <div class="page-section space-y-8">
            <section class="bw-card relative isolate flex flex-col gap-8 overflow-hidden p-6 sm:flex-row sm:p-10">
                @if ($book->cover_url)<img src="{{ $book->cover_src }}" alt="" aria-hidden="true" class="pointer-events-none absolute inset-0 -z-10 h-full w-full scale-125 object-cover opacity-[0.12] blur-3xl">@endif
                @if ($book->cover_url)
                    <img src="{{ $book->cover_src }}" data-cover-fallback="{{ Str::limit($book->title, 40) }}" alt="Cover of {{ $book->title }}" class="mx-auto w-44 shrink-0 self-start rounded-lg object-cover shadow-[0_30px_50px_-20px_rgba(42,26,29,0.7)] ring-1 ring-black/10 sm:mx-0 sm:w-52 sm:-rotate-2">
                @else
                    <div class="mx-auto flex aspect-[2/3] w-44 shrink-0 items-center justify-center self-start rounded-lg bg-gradient-to-br from-burgundy via-[#8B2A3D] to-burgundy-deep p-5 text-center font-serif text-xl font-semibold text-[#FFF8EF] shadow-[0_30px_50px_-20px_rgba(42,26,29,0.7)] sm:mx-0 sm:w-52 sm:-rotate-2">
                        {{ $book->title }}
                    </div>
                @endif

                <div class="flex-1 self-center text-center sm:text-left">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-tan dark:text-[#c7a491]">Book details</p>
                    <h1 class="font-display mt-2 text-4xl leading-none sm:text-6xl text-ink dark:text-[#f4e9e2] font-bold">{{ $book->title }}</h1>
                    <p class="mt-3 text-lg text-walnut dark:text-[#d4c2b9]">
                        {{ $book->author_name ?? 'Unknown author' }}
                        @if ($book->first_publish_year)
                            <span aria-hidden="true">&middot;</span> {{ $book->first_publish_year }}
                        @endif
                    </p>
                    <div class="mt-5 inline-flex items-center gap-2 rounded-full bg-paper/80 px-4 py-2 shadow-sm ring-1 ring-warmborder dark:bg-white/5 dark:ring-[#4B3035]">
                        @if ($count)
                            <x-star-display :rating="$average" />
                            <span class="font-serif text-lg font-semibold text-ink dark:text-[#f4e9e2]">{{ number_format($average, 1) }}</span>
                            <span class="text-xs text-tan dark:text-[#c1aaa0]">{{ $count }} {{ Str::plural('review', $count) }}</span>
                        @else
                            <span class="text-sm text-[#8a7065] dark:text-[#c1aaa0]">No reviews yet</span>
                        @endif
                    </div>
                    @if ($myShelfItem?->status === 'reading' && $myShelfItem->total_pages)
                        @php($bookProgress = min(100, (int) round(($myShelfItem->current_page ?? 0) / $myShelfItem->total_pages * 100)))
                        <p class="mt-4 text-sm text-tan dark:text-[#c7a491]">You’re {{ $bookProgress }}% through this one.</p>
                        <div class="mt-2 h-1.5 max-w-sm overflow-hidden rounded-full bg-parchment dark:bg-[#493536]" aria-label="{{ $bookProgress }}% read"><span class="block h-full rounded-full bg-burgundy" style="width: {{ $bookProgress }}%"></span></div>
                    @endif
                    <form method="POST" action="{{ route('books.shelf.update', $book) }}" class="mt-6 max-w-xl rounded-2xl border border-warmborder bg-paper/80 p-4 text-left backdrop-blur dark:border-[#4B3035] dark:bg-[#201317]/70">
                        @csrf
                        <label for="book-shelf-status" class="block text-xs font-semibold uppercase tracking-wide text-tan dark:text-[#c7a491]">Your tree</label>
                        <div class="mt-2 flex flex-col gap-2 sm:flex-row">
                            <select id="book-shelf-status" name="status" class="bw-input min-h-11 flex-1 text-sm text-ink focus:border-burgundy focus:ring-burgundy/20 dark:border-[#4B3035] dark:bg-[#201317] dark:text-[#F4E9E2]">
                                @foreach ($shelfStatuses as $key => $label)
                                    <option value="{{ $key }}" @selected(($myShelfItem?->status ?? 'want_to_read') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn-primary">{{ $myShelfItem ? 'Update on my tree' : 'Add to my tree' }}</button>
                        </div>
                        <p class="mt-2 text-xs text-tan dark:text-[#c7a491]">
                            @if ($myShelfItem)
                                It’s on your tree. <a href="{{ route('users.show', auth()->user()->username) }}#bookshelf" class="font-semibold text-burgundy underline underline-offset-2 dark:text-burgundy-dark">Open it there</a> to move your bookmark or write a note.
                            @else
                                It will appear on a branch of your tree straight away.
                            @endif
                        </p>
                    </form>
                    <div class="mt-4 flex flex-wrap gap-2">
                        <a href="{{ route('buddy.index', ['book' => $book->id]) }}" class="bw-chip">🤝 Start a buddy read</a>
                        <a href="{{ route('feed.index') }}#composer" class="bw-chip">❝ Share a quote</a>
                    </div>
                </div>
            </section>

            <div class="grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem]">
                <div class="min-w-0 space-y-8">
            <section class="bw-card p-6 sm:p-8">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-tan dark:text-[#c7a491]">Your review</p>
                <h2 class="font-display mt-1 text-3xl text-ink dark:text-[#f4e9e2] font-bold">
                    {{ $myReview ? 'Edit your review' : 'Rate this book' }}
                </h2>
                <form method="POST" action="{{ route('reviews.store', $book) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <x-input-label value="Your rating" />
                        <div class="mt-2"><x-star-input :value="old('rating', $myReview->rating ?? 0)" /></div>
                        <x-input-error :messages="$errors->get('rating')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="review-body" value="Your thoughts (optional)" />
                        <textarea id="review-body" name="body" rows="4" maxlength="3000"
                                  class="mt-1 block w-full rounded-lg border-warmborder bg-paper text-sm text-ink shadow-sm focus:border-burgundy focus:ring-burgundy/20 dark:border-[#4B3035] dark:bg-[#201317] dark:text-[#f4e9e2]">{{ old('body', $myReview->body ?? '') }}</textarea>
                        <x-input-error :messages="$errors->get('body')" class="mt-2" />
                    </div>
                    <x-primary-button>{{ $myReview ? 'Update review' : 'Save review' }}</x-primary-button>
                </form>
                @if ($myReview)
                    <form method="POST" action="{{ route('reviews.destroy', $myReview) }}" class="mt-3" onsubmit="return confirm('Delete your review?')">
                        @csrf
                        @method('DELETE')
                        <button class="text-sm text-red-600 underline">Delete my review</button>
                    </form>
                @endif
            </section>
            <section aria-labelledby="book-quotes-heading" class="bw-card p-6 sm:p-8">
                <div class="flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-tan dark:text-[#c7a491]">Passages worth keeping</p>
                        <h2 id="book-quotes-heading" class="font-display mt-1 text-3xl text-ink dark:text-[#f4e9e2] font-bold">Quotes from this book</h2>
                    </div>
                    <span class="text-sm text-tan dark:text-[#c1aaa0]">{{ $quotes->total() }} {{ Str::plural('quote', $quotes->total()) }}</span>
                </div>
                <p class="mt-2 text-sm leading-6 text-walnut dark:text-[#d4c2b9]">Your saved passages stay private unless you turn on sharing. Quotes shared by other readers appear here too.</p>
                @if ($quotes->isNotEmpty())
                    <div class="mt-5 grid gap-4 md:grid-cols-2">
                        @foreach ($quotes as $quote)
                            @php($highlight = $quote->highlight)
                            <article class="flex flex-col rounded-xl border border-warmborder bg-paper p-4 dark:border-[#4B3035] dark:bg-[#201317] sm:p-5">
                                <blockquote class="flex-1 font-serif text-lg italic leading-7 text-ink dark:text-[#F4E9E2]">“{{ $highlight->text }}”</blockquote>
                                <p class="mt-3 text-xs text-tan dark:text-[#c7a491]">Page {{ $highlight->page_number }} · Kept by {{ $quote->user->name }}</p>
                                <div class="mt-4 flex flex-wrap items-center gap-2 border-t border-warmborder pt-3 dark:border-[#4B3035]">
                                    <button type="button" class="focus-ring rounded-full border border-burgundy/30 px-3 py-2 text-xs font-semibold text-burgundy hover:bg-rosetint dark:text-burgundy-dark" data-download-quote data-quote="{{ $highlight->text }}" data-title="{{ $book->title }}" data-author="{{ $book->author_name ?? '' }}" data-reader="{{ $quote->user->name }}" data-page="{{ $highlight->page_number }}">Download card</button>
                                    @if ($quote->user_id === auth()->id())
                                        <form method="POST" action="{{ route('quotes.share', $quote) }}">
                                            @csrf
                                            <button type="submit" class="focus-ring rounded-full px-3 py-2 text-xs font-semibold {{ $quote->shared_at ? 'bg-burgundy text-white' : 'bg-parchment text-walnut dark:bg-[#382329] dark:text-[#F4E9E2]' }}">
                                                {{ $quote->shared_at ? 'Sharing on · turn off' : 'Share quote' }}
                                            </button>
                                        </form>
                                        @if ($quote->shared_at)
                                            <button type="button" class="focus-ring rounded-full px-2 py-2 text-xs font-semibold text-burgundy underline dark:text-burgundy-dark" data-copy-link="{{ route('quotes.public', $quote->share_token) }}">Copy link</button>
                                        @endif
                                        <form method="POST" action="{{ route('quotes.destroy', $quote) }}" class="ml-auto">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="focus-ring rounded-full px-2 py-2 text-xs font-semibold text-red-700 underline dark:text-red-300">Remove</button>
                                        </form>
                                    @elseif ($quote->shared_at)
                                        <span class="rounded-full bg-rosetint px-3 py-1.5 text-xs font-semibold text-burgundy dark:bg-[#382329] dark:text-burgundy-dark">Shared quote</span>
                                    @endif
                                </div>
                            </article>
                        @endforeach
                    </div>
                    @if ($quotes->hasPages())
                        <div class="mt-4">{{ $quotes->withQueryString()->links() }}</div>
                    @endif
                @else
                    <div class="mt-5 rounded-xl border border-dashed border-warmborder bg-parchment/50 px-4 py-6 text-center dark:border-[#4B3035] dark:bg-[#201317]">
                        <p class="font-serif text-lg font-semibold text-ink dark:text-[#F4E9E2]">No saved passages from this title yet.</p>
                        <p class="mt-1 text-sm text-tan dark:text-[#c7a491]">Read a private PDF, highlight a line, and save it as a quote to keep it here.</p>
                    </div>
                @endif
            </section>
            <section class="bw-card p-6 sm:p-8">
                <div class="flex items-baseline justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-tan dark:text-[#c7a491]">Reader conversation</p>
                        <h2 class="font-display mt-1 text-3xl text-ink dark:text-[#f4e9e2] font-bold">Reader reviews</h2>
                    </div>
                    <span class="text-sm text-tan dark:text-[#c1aaa0]">{{ $count }} {{ Str::plural('review', $count) }}</span>
                </div>
                <div class="mt-4 divide-y divide-[#f0e8df] dark:divide-[#453538]">
                    @forelse ($reviews as $review)
                        <article class="py-4 first:pt-0 last:pb-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <a href="{{ route('users.show', $review->user->username) }}"
                                   class="focus-ring rounded-sm text-sm font-semibold text-burgundy hover:underline dark:text-burgundy-dark">
                                    {{ '@' . $review->user->username }}
                                </a>
                                <x-star-display :rating="$review->rating" />
                                <span class="text-xs text-[#9a8579] dark:text-[#b39d93]">{{ $review->created_at->diffForHumans() }}</span>
                            </div>
                            @if ($review->body)
                                <p class="mt-2 whitespace-pre-line break-words text-sm leading-6 text-[#564541] dark:text-[#e3d3ca]">{{ $review->body }}</p>
                            @endif
                        </article>
                    @empty
                        <p class="py-5 text-sm text-[#8a7065] dark:text-[#c1aaa0]">Be the first to review this book.</p>
                    @endforelse
                </div>
                @if ($reviews->hasPages())
                    <div class="mt-4 border-t border-[#f0e8df] pt-4 dark:border-[#453538]">{{ $reviews->links() }}</div>
                @endif
            </section>
                </div>
                <aside class="space-y-8 lg:sticky lg:top-28 lg:self-start">
            <section aria-labelledby="private-pdfs-heading" class="bw-card p-6">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-tan dark:text-[#c7a491]">Private to your account</p>
                <h2 id="private-pdfs-heading" class="mt-1 font-serif text-2xl font-semibold text-ink dark:text-[#f4e9e2]">Your PDFs for this book</h2>
                <p id="book-pdf-help" class="mt-2 text-sm text-[#8a7065] dark:text-[#c1aaa0]">
                    Upload up to 5 PDF files at a time (20 MB each). Only you can view, download, or delete your files.
                </p>

                <form method="POST" action="{{ route('books.pdfs.store', $book) }}" enctype="multipart/form-data" class="mt-4 space-y-3">
                    @csrf
                    <div>
                        <label for="book-pdfs" class="block text-sm font-medium text-ink dark:text-[#f4e9e2]">Choose PDF files</label>
                        <input id="book-pdfs" name="pdfs[]" type="file" accept="application/pdf,.pdf" multiple required
                               aria-describedby="book-pdf-help book-pdf-errors"
                               class="focus-ring mt-1 block min-h-11 w-full rounded-lg text-sm text-walnut file:mr-4 file:rounded-full file:border-0 file:bg-parchment file:px-4 file:py-2 file:font-semibold file:text-burgundy hover:file:bg-rosetint dark:text-[#d4c2b9] dark:focus:ring-offset-raised">
                    </div>
                    <div id="book-pdf-errors" role="alert" aria-live="polite">
                        @foreach ($errors->get('pdfs') as $error)
                            <p class="text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
                        @endforeach
                        @foreach ($errors->getMessages() as $field => $messages)
                            @if (str_starts_with($field, 'pdfs.'))
                                @foreach ($messages as $error)
                                    <p class="text-sm text-red-600 dark:text-red-400">{{ $error }}</p>
                                @endforeach
                            @endif
                        @endforeach
                    </div>
                    <button type="submit" class="focus-ring inline-flex min-h-11 items-center justify-center rounded-full bg-burgundy px-5 py-2 text-sm font-semibold text-[#fff8ef] transition hover:bg-burgundy-hover">
                        Upload PDFs
                    </button>
                </form>

                @if ($myPdfs->isNotEmpty())
                    <ul class="mt-5 divide-y divide-[#f0e8df] dark:divide-[#453538]" aria-label="Your private PDF files">
                        @foreach ($myPdfs as $pdf)
                            <li class="flex flex-wrap items-center justify-between gap-3 py-3 first:pt-0 last:pb-0">
                                <div class="min-w-0">
                                    <p class="break-words text-sm font-medium text-[#392b2a] dark:text-[#f4e9e2]">{{ $pdf->original_name }}</p>
                                    <p class="mt-1 text-xs text-[#8a7065] dark:text-[#c1aaa0]">Added {{ $pdf->created_at->diffForHumans() }}</p>
                                </div>
                                <div class="flex shrink-0 items-center gap-4">
                                    <a href="{{ route('books.pdfs.reader', [$book, $pdf]) }}"
                                       class="focus-ring inline-flex min-h-11 items-center rounded-md px-2 text-sm font-semibold text-burgundy underline hover:text-burgundy-hover dark:text-burgundy-dark">
                                        Read
                                        <span class="sr-only">{{ $pdf->original_name }}</span>
                                    </a>
                                    <a href="{{ route('books.pdfs.download', [$book, $pdf]) }}"
                                       class="focus-ring inline-flex min-h-11 items-center rounded-md px-2 text-sm font-semibold text-burgundy underline hover:text-burgundy-hover dark:text-burgundy-dark">
                                        Download
                                        <span class="sr-only">{{ $pdf->original_name }}</span>
                                    </a>
                                    <form method="POST" action="{{ route('books.pdfs.destroy', [$book, $pdf]) }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="inline-flex min-h-11 items-center rounded-md px-2 text-sm font-semibold text-red-700 underline hover:text-red-900 focus:outline-none focus:ring-2 focus:ring-red-500 dark:text-red-300 dark:hover:text-red-200">
                                            Delete
                                            <span class="sr-only">{{ $pdf->original_name }}</span>
                                        </button>
                                    </form>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @else
                    <p class="mt-5 rounded-xl border border-dashed border-[#d9c6b6] px-4 py-5 text-center text-sm text-[#8a7065] dark:border-[#594749] dark:text-[#c1aaa0]">
                        You haven’t uploaded any PDFs for this book yet.
                    </p>
                @endif
            </section>
                </aside>
            </div>
    </div>
</x-app-layout>
