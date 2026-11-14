<x-app-layout>
    @push('scripts')
        @vite('resources/js/pdf-reader.js')
    @endpush

    <x-slot name="header">
        <x-page-header :eyebrow="$book->title" :title="$bookPdf->original_name">
            <x-slot name="actions">
                <a href="{{ route('books.show', $book) }}" class="btn-ghost"><span aria-hidden="true">&larr;</span> Back to book</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="py-6 sm:py-8">
        <div class="page-shell space-y-4" data-pdf-reader
              data-file-url="{{ $fileUrl }}"
              data-highlights-url="{{ $highlightsUrl }}"
              data-save-quote-url="{{ route('quotes.store', [$bookPdf, '__HIGHLIGHT__']) }}"
              data-store-highlight-url="{{ $storeHighlightUrl }}"
              data-progress-url="{{ $progressUrl }}"
              data-reading-status="{{ $readingStatus }}"
              data-initial-page="{{ $initialPage }}"
              data-delete-highlight-url="{{ route('books.pdfs.highlights.index', [$book, $bookPdf], false) }}/__HIGHLIGHT__">
            <section aria-label="PDF reader controls" class="bookplate flex flex-wrap items-center gap-2 rounded-xl p-3">
                <button type="button" data-previous-page disabled class="min-h-11 rounded-lg border border-[#c9a58f] px-3 text-sm font-semibold text-[#713640] focus:outline-none focus:ring-2 focus:ring-[#b98768] focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:text-[#f0d8c1] dark:focus:ring-offset-[#291f20]">
                    Previous page
                </button>
                <span class="min-w-24 text-center text-sm text-[#564541] dark:text-[#e3d3ca]" aria-live="polite">
                    Page <span data-current-page>–</span> of <span data-page-count>–</span>
                </span>
                <button type="button" data-next-page disabled class="min-h-11 rounded-lg border border-[#c9a58f] px-3 text-sm font-semibold text-[#713640] focus:outline-none focus:ring-2 focus:ring-[#b98768] focus:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50 dark:text-[#f0d8c1] dark:focus:ring-offset-[#291f20]">
                    Next page
                </button>
                <span class="hidden h-7 border-l border-[#eadfd4] sm:block dark:border-[#453538]" aria-hidden="true"></span>
                <button type="button" data-zoom-out disabled aria-label="Zoom out" class="min-h-11 min-w-11 rounded-lg border border-[#c9a58f] text-lg font-semibold text-[#713640] focus:outline-none focus:ring-2 focus:ring-[#b98768] focus:ring-offset-2 disabled:opacity-50 dark:text-[#f0d8c1] dark:focus:ring-offset-[#291f20]">−</button>
                <button type="button" data-zoom-in disabled aria-label="Zoom in" class="min-h-11 min-w-11 rounded-lg border border-[#c9a58f] text-lg font-semibold text-[#713640] focus:outline-none focus:ring-2 focus:ring-[#b98768] focus:ring-offset-2 disabled:opacity-50 dark:text-[#f0d8c1] dark:focus:ring-offset-[#291f20]">+</button>
                <span class="ml-auto text-xs text-[#8a7065] dark:text-[#c1aaa0]" data-zoom-label>Fit to page</span>
                <button type="button" data-save-highlight disabled class="min-h-11 rounded-full bg-[#5b1f2b] px-4 text-sm font-semibold text-[#fff8ef] disabled:cursor-not-allowed disabled:opacity-50 focus:outline-none focus:ring-2 focus:ring-[#b98768] focus:ring-offset-2 dark:focus:ring-offset-[#291f20]">
                    Highlight selection
                </button>
            </section>

            <p data-reader-status role="status" aria-live="polite" class="min-h-5 text-sm text-[#713640] dark:text-[#e4b3a5]">
                Loading your private PDF…
            </p>

            <div class="grid items-start gap-4 lg:grid-cols-[minmax(0,1fr)_18rem]">
                <section aria-label="PDF page" class="min-w-0 overflow-auto rounded-xl border border-warmborder bg-parchment p-3 sm:p-5 dark:border-[#453538] dark:bg-[#241b1c]">
                    <div data-page-viewport class="pdf-reader-page mx-auto"></div>
                </section>

                <aside aria-labelledby="saved-highlights-heading" class="bookplate rounded-3xl p-4">
                    <h2 id="saved-highlights-heading" class="font-serif text-xl font-semibold text-ink dark:text-[#f4e9e2]">Saved highlights</h2>
                    <p class="mt-1 text-sm text-[#8a7065] dark:text-[#c1aaa0]">Select text in the PDF, then choose “Highlight selection”. Your highlights are private to you.</p>
                    <p data-highlights-empty class="mt-4 rounded-xl border border-dashed border-[#d9c6b6] px-3 py-4 text-center text-sm text-[#8a7065] dark:border-[#594749] dark:text-[#c1aaa0]">
                        No highlights yet.
                    </p>
                    <ul data-highlights-list class="mt-4 space-y-3"></ul>
                </aside>
            </div>
        </div>
    </div>
</x-app-layout>
