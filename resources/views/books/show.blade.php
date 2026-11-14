<x-app-layout>
    @push('scripts')
        <script>window.bookwormBarnabyContext = @js(['book_id' => $book->id, 'title' => $book->title]);</script>
    @endpush
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
                        <button type="button" class="bw-chip" data-ask-barnaby="Would I enjoy “{{ $book->title }}”{{ $book->author_name ? ' by ' . $book->author_name : '' }}? Tell me a little about it, spoiler-free, and how it fits my shelf.">
                            <x-barnaby-avatar class="h-5 w-5" /> Ask Barnaby
                        </button>
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
            <section id="private-pdfs" aria-labelledby="private-pdfs-heading" class="bw-card scroll-mt-28 overflow-hidden">
                <div class="p-5 pb-4">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <h2 id="private-pdfs-heading" class="font-serif text-2xl font-semibold text-ink dark:text-[#f4e9e2]">Your PDFs for this book</h2>
                            <p class="mt-1 inline-flex items-center gap-1.5 text-xs font-semibold text-tan">
                                <svg class="h-3.5 w-3.5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><rect x="5" y="11" width="14" height="10" rx="2"/><path d="M8 11V8a4 4 0 0 1 8 0v3"/></svg>
                                Private — only you can see these
                            </p>
                        </div>
                        @if ($myPdfs->isNotEmpty())
                            <span class="rounded-full bg-parchment px-2.5 py-1 text-xs font-semibold text-walnut dark:bg-white/10 dark:text-[#e3d3ca]">{{ $myPdfs->count() }}</span>
                        @endif
                    </div>

                    @if ($myPdfs->isNotEmpty())
                        <ul class="mt-4 space-y-2" aria-label="Your private PDF files">
                            @foreach ($myPdfs as $pdf)
                                <li class="group flex items-center gap-3 rounded-2xl border border-warmborder bg-paper p-2.5 transition hover:border-burgundy/30 dark:border-[#4B3035] dark:bg-white/[0.03]" x-data="{ menu: false }">
                                    <a href="{{ route('books.pdfs.reader', [$book, $pdf]) }}" class="flex h-12 w-10 shrink-0 flex-col items-center justify-center rounded-lg bg-gradient-to-b from-[#b4232c] to-[#7d1820] text-[9px] font-bold tracking-wide text-white shadow-sm" aria-hidden="true" tabindex="-1">
                                        <svg class="mb-0.5 h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8z"/><path d="M14 3v5h5"/></svg>
                                        PDF
                                    </a>
                                    <div class="min-w-0 flex-1">
                                        <a href="{{ route('books.pdfs.reader', [$book, $pdf]) }}" class="block truncate text-sm font-semibold text-ink hover:text-burgundy dark:text-[#f4e9e2] dark:hover:text-[#E9A2AE]" title="{{ $pdf->original_name }}">{{ $pdf->original_name }}</a>
                                        <p class="truncate text-xs text-tan">{{ collect([$pdf->sizeLabel(), 'added ' . $pdf->created_at->diffForHumans()])->filter()->implode(' · ') }}</p>
                                    </div>
                                    <a href="{{ route('books.pdfs.reader', [$book, $pdf]) }}" class="btn-primary min-h-0 shrink-0 px-4 py-1.5 text-xs">
                                        Read<span class="sr-only"> {{ $pdf->original_name }}</span>
                                    </a>
                                    <div class="relative shrink-0">
                                        <button type="button" @click="menu = !menu" @click.outside="menu = false" class="ig-icon !h-8 !w-8" :aria-expanded="menu" aria-label="More options for {{ $pdf->original_name }}">
                                            <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="12" cy="5" r="1.2" fill="currentColor"/><circle cx="12" cy="12" r="1.2" fill="currentColor"/><circle cx="12" cy="19" r="1.2" fill="currentColor"/></svg>
                                        </button>
                                        <div x-show="menu" x-cloak x-transition.origin.top.right class="absolute right-0 z-20 mt-1 w-40 overflow-hidden rounded-2xl border border-warmborder bg-paper py-1 text-sm shadow-xl dark:border-[#4B3035] dark:bg-raised">
                                            <a href="{{ route('books.pdfs.download', [$book, $pdf]) }}" class="block px-4 py-2.5 hover:bg-parchment dark:hover:bg-white/5">Download<span class="sr-only"> {{ $pdf->original_name }}</span></a>
                                            <form method="POST" action="{{ route('books.pdfs.destroy', [$book, $pdf]) }}" onsubmit="return confirm('Delete “{{ addslashes($pdf->original_name) }}”? Its highlights will be deleted too.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="block w-full px-4 py-2.5 text-left font-semibold text-red-700 hover:bg-parchment dark:text-red-300 dark:hover:bg-white/5">Delete<span class="sr-only"> {{ $pdf->original_name }}</span></button>
                                            </form>
                                        </div>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                {{-- Upload: drag & drop, check files before sending, show progress --}}
                <form method="POST" action="{{ route('books.pdfs.store', $book) }}" enctype="multipart/form-data"
                      class="border-t border-warmborder bg-parchment/35 p-5 dark:border-[#4B3035] dark:bg-white/[0.02]"
                      x-data="pdfUploader({ maxFiles: 5, maxMb: 20 })" @submit.prevent="upload($el)" data-scroll-top>
                    @csrf
                    <input id="book-pdfs" x-ref="input" name="pdfs[]" type="file" accept="application/pdf,.pdf" multiple class="sr-only"
                           aria-describedby="book-pdf-help book-pdf-errors" @change="add($event.target.files); $event.target.value = ''">

                    <label for="book-pdfs" x-show="!files.length" class="flex cursor-pointer flex-col items-center gap-2 rounded-2xl border-2 border-dashed border-[#d9c6b6] bg-paper/70 px-4 py-6 text-center transition hover:border-burgundy/50 dark:border-[#594749] dark:bg-white/[0.03]"
                           :class="over && '!border-burgundy bg-rosetint/50 dark:!bg-white/10'"
                           @dragover.prevent="over = true" @dragleave.prevent="over = false" @drop.prevent="over = false; add($event.dataTransfer.files)">
                        <span class="flex h-12 w-12 items-center justify-center rounded-full bg-parchment text-burgundy dark:bg-white/10 dark:text-[#E9A2AE]" aria-hidden="true">
                            <svg class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"><path d="M12 16V4m0 0-4 4m4-4 4 4"/><path d="M4 16v2a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2v-2"/></svg>
                        </span>
                        <span class="text-sm font-semibold text-ink dark:text-[#f4e9e2]">{{ $myPdfs->isEmpty() ? 'Add the PDF of this book' : 'Add another PDF' }}</span>
                        <span class="text-xs text-tan">Drop it here or <span class="font-semibold text-burgundy underline dark:text-[#E9A2AE]">browse</span></span>
                    </label>
                    <p id="book-pdf-help" class="mt-2 text-center text-xs text-tan" x-show="!files.length">Up to 5 files at once, 20 MB each.</p>

                    {{-- Chosen files, checked before upload --}}
                    <ul x-show="files.length" x-cloak class="space-y-2">
                        <template x-for="(entry, index) in files" :key="entry.id">
                            <li class="flex items-center gap-3 rounded-xl bg-paper px-3 py-2 dark:bg-white/5" :class="entry.error && 'ring-1 ring-red-300 dark:ring-red-800'">
                                <span class="flex h-8 w-7 shrink-0 items-center justify-center rounded bg-[#b4232c] text-[8px] font-bold text-white" aria-hidden="true">PDF</span>
                                <span class="min-w-0 flex-1">
                                    <span class="block truncate text-sm font-semibold text-ink dark:text-[#f4e9e2]" x-text="entry.file.name"></span>
                                    <span class="block text-xs" :class="entry.error ? 'text-red-600 dark:text-red-400' : 'text-tan'" x-text="entry.error || size(entry.file.size)"></span>
                                </span>
                                <button type="button" x-show="!uploading" @click="remove(index)" class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full text-tan hover:bg-parchment hover:text-burgundy dark:hover:bg-white/10" :aria-label="`Remove ${entry.file.name}`">×</button>
                            </li>
                        </template>
                    </ul>

                    <div x-show="uploading" x-cloak class="mt-3" role="progressbar" aria-label="Upload progress" aria-valuemin="0" aria-valuemax="100" :aria-valuenow="progress">
                        <div class="h-2 overflow-hidden rounded-full bg-paper dark:bg-white/10"><div class="h-full rounded-full bg-burgundy transition-all dark:bg-burgundy-dark" :style="`width: ${progress}%`"></div></div>
                        <p class="mt-1 text-xs text-tan" x-text="progress < 100 ? `Uploading… ${progress}%` : 'Finishing up…'"></p>
                    </div>

                    <div id="book-pdf-errors" role="alert" aria-live="polite" class="mt-2 space-y-1">
                        <template x-for="message in errors" :key="message"><p class="text-sm text-red-600 dark:text-red-400" x-text="message"></p></template>
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

                    <div x-show="files.length" x-cloak class="mt-3 flex items-center gap-2">
                        <label for="book-pdfs" x-show="!uploading && files.length < maxFiles" class="btn-ghost min-h-0 cursor-pointer py-2 text-xs">+ Add more</label>
                        <button type="submit" class="btn-primary ml-auto min-h-0 py-2" :disabled="uploading || !validFiles().length"
                                x-text="uploading ? 'Uploading…' : `Upload ${validFiles().length} ${validFiles().length === 1 ? 'PDF' : 'PDFs'}`">Upload PDFs</button>
                    </div>
                    <noscript><button type="submit" class="btn-primary mt-3">Upload PDFs</button></noscript>
                </form>
            </section>
                </aside>
            </div>
    </div>
</x-app-layout>
