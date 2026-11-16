{{-- EPUB reader: the book as real pages, with themes, fonts, size, contents and Barnaby. --}}
<x-app-layout>
    @push('scripts')
        @vite(['resources/css/reader.css', 'resources/js/epub-reader.js'])
    @endpush
    <x-slot name="title">{{ $book->title }} · Reading</x-slot>

    <div class="page-shell py-4 sm:py-6">
        <div class="epub" data-epub-reader
             data-file-url="{{ $fileUrl }}"
             data-progress-url="{{ $progressUrl }}"
             data-reading-status="{{ $readingStatus }}"
             data-total-pages="{{ (int) (auth()->user()->shelfItems()->where('book_id', $book->id)->value('total_pages') ?: $book->page_count ?: 0) }}"
             data-book-key="epub-{{ $bookPdf->id }}"
             data-book-title="{{ $book->title }}"
             data-barnaby-recap-url="{{ route('barnaby.recap', $book) }}"
             data-theme="paper">
            <header class="epub__bar">
                <a href="{{ route('books.show', $book) }}" class="epub__btn" aria-label="Back to {{ $book->title }}">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                </a>
                <div class="min-w-0 flex-1">
                    <p class="epub__title">{{ $book->title }}</p>
                    <p class="epub__chapter" data-epub-chapter>Opening…</p>
                </div>
                <button type="button" class="epub__btn" data-epub-toggle="toc" aria-label="Contents">☰</button>
                <button type="button" class="epub__btn" data-epub-toggle="settings" aria-label="Reading settings"><span class="font-serif font-bold">Aa</span></button>
                <button type="button" class="epub__btn" data-epub-recap aria-label="Story so far (Barnaby)"><x-barnaby-avatar class="h-7 w-7" /></button>
            </header>

            <div class="epub__stage">
                <button type="button" class="epub__turn epub__turn--prev" data-epub-prev aria-label="Previous page">‹</button>
                <div class="epub__page" data-epub-view></div>
                <button type="button" class="epub__turn epub__turn--next" data-epub-next aria-label="Next page">›</button>

                <aside class="epub__panel" data-epub-panel="toc" hidden>
                    <h2>Contents</h2>
                    <ol data-epub-toc></ol>
                </aside>
                <aside class="epub__panel" data-epub-panel="settings" hidden>
                    <h2>Reading settings</h2>
                    <p class="epub__label">Theme</p>
                    <div class="epub__choices">
                        @foreach (['paper' => 'Paper', 'sepia' => 'Sepia', 'night' => 'Night'] as $key => $label)
                            <button type="button" data-epub-theme="{{ $key }}" class="epub__swatch epub__swatch--{{ $key }}">{{ $label }}</button>
                        @endforeach
                    </div>
                    <p class="epub__label">Font</p>
                    <div class="epub__choices">
                        @foreach (['Literata' => 'Literata', 'EB Garamond' => 'Garamond', 'Atkinson Hyperlegible' => 'Clear', 'original' => 'Book’s own'] as $font => $label)
                            <button type="button" data-epub-font="{{ $font }}">{{ $label }}</button>
                        @endforeach
                    </div>
                    <p class="epub__label">Text size</p>
                    <div class="epub__choices">
                        <button type="button" data-epub-size="-1" aria-label="Smaller text">A−</button>
                        <span data-epub-size-label>100%</span>
                        <button type="button" data-epub-size="1" aria-label="Larger text">A+</button>
                    </div>
                </aside>
                <aside class="epub__panel epub__panel--barnaby" data-epub-panel="barnaby" hidden>
                    <h2 class="flex items-center gap-2"><x-barnaby-avatar class="h-8 w-8" /> Story so far</h2>
                    <div class="barnaby-msg__bubble" data-epub-barnaby></div>
                </aside>
            </div>

            <footer class="epub__foot">
                <div class="epub__progress"><span data-epub-bar></span></div>
                <span data-epub-percent>0%</span>
            </footer>
        </div>
    </div>
</x-app-layout>
