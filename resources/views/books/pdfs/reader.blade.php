<x-app-layout>
    @push('scripts')
        @vite('resources/js/pdf-reader.js')
    @endpush

    <div class="page-shell py-4 sm:py-6">
        <div class="reader" data-pdf-reader data-theme="paper"
              data-file-url="{{ $fileUrl }}"
              data-highlights-url="{{ $highlightsUrl }}"
              data-save-quote-url="{{ route('quotes.store', [$bookPdf, '__HIGHLIGHT__']) }}"
              data-store-highlight-url="{{ $storeHighlightUrl }}"
              data-progress-url="{{ $progressUrl }}"
              data-reading-status="{{ $readingStatus }}"
              data-initial-page="{{ $initialPage }}"
              data-book-title="{{ $book->title }}"
              data-book-author="{{ $book->author_name }}"
              data-delete-highlight-url="{{ route('books.pdfs.highlights.index', [$book, $bookPdf], false) }}/__HIGHLIGHT__">

            {{-- Top bar --}}
            <header class="reader-bar">
                <a href="{{ route('books.show', $book) }}" class="reader-btn reader-btn--icon" aria-label="Back to {{ $book->title }}" title="Back to book">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                </a>
                <div class="reader-bar__title">
                    <p class="reader-bar__book">{{ $book->title }}</p>
                    <p class="reader-bar__file">{{ $bookPdf->original_name }}</p>
                </div>

                <div class="reader-segmented" role="group" aria-label="Reading view">
                    <button type="button" data-view-option="book" aria-pressed="true">Book</button>
                    <button type="button" data-view-option="pdf" aria-pressed="false">Original</button>
                </div>

                <button type="button" class="reader-btn reader-btn--icon" data-toggle-panel="settings" aria-expanded="false" aria-controls="reader-settings" aria-label="Reading settings" title="Reading settings">
                    <span class="reader-aa" aria-hidden="true">Aa</span>
                </button>
                <button type="button" class="reader-btn reader-btn--icon" data-toggle-panel="highlights" aria-expanded="false" aria-controls="reader-highlights" aria-label="Saved highlights" title="Saved highlights">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 3h12v18l-6-4-6 4z"/></svg>
                    <span class="reader-badge" data-highlight-count hidden>0</span>
                </button>
                <button type="button" class="reader-btn reader-btn--icon" data-fullscreen aria-label="Full screen" title="Full screen">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"/></svg>
                </button>
            </header>

            {{-- Book / PDF stage --}}
            <div class="reader-stage" data-stage>
                <button type="button" class="reader-turn reader-turn--prev" data-previous-page disabled aria-label="Previous page">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                </button>

                <div class="reader-book" data-book-view hidden>
                    <div class="reader-book__clip" data-book-clip>
                        <div class="reader-flow" data-flow lang="{{ str_replace('_', '-', app()->getLocale()) }}"></div>
                    </div>
                    <div class="reader-book__folios" aria-hidden="true">
                        <span data-folio-left></span>
                        <span data-folio-right></span>
                    </div>
                </div>

                <div class="reader-pdf" data-pdf-view hidden>
                    <div data-page-viewport class="pdf-reader-page"></div>
                </div>

                <div class="reader-loading" data-loading>
                    <span class="reader-spinner" aria-hidden="true"></span>
                    <p data-loading-label>Opening your book…</p>
                </div>

                <button type="button" class="reader-turn reader-turn--next" data-next-page disabled aria-label="Next page">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
                </button>
            </div>

            {{-- Progress --}}
            <footer class="reader-foot">
                <label class="sr-only" for="reader-scrubber">Go to page</label>
                <input id="reader-scrubber" type="range" min="1" max="1" value="1" step="1" data-scrubber disabled>
                <p class="reader-foot__label" aria-live="polite">
                    Page <span data-current-page>–</span> of <span data-page-count>–</span>
                    <span class="reader-foot__extra" data-page-extra></span>
                </p>
            </footer>

            <p data-reader-status role="status" aria-live="polite" class="sr-only"></p>

            {{-- Reading settings --}}
            <section id="reader-settings" class="reader-panel reader-panel--settings" data-panel="settings" hidden aria-label="Reading settings">
                <div class="reader-panel__head">
                    <h2>Reading settings</h2>
                    <button type="button" class="reader-btn reader-btn--icon" data-close-panel aria-label="Close settings">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
                    </button>
                </div>

                <div class="reader-setting" data-book-only>
                    <p class="reader-setting__label">Font</p>
                    <div class="reader-fonts" role="radiogroup" aria-label="Font">
                        <button type="button" role="radio" data-setting="font" data-value="literata" style="font-family: Literata, Georgia, serif">Literata</button>
                        <button type="button" role="radio" data-setting="font" data-value="garamond" style="font-family: 'EB Garamond', Garamond, serif">Garamond</button>
                        <button type="button" role="radio" data-setting="font" data-value="georgia" style="font-family: Georgia, serif">Georgia</button>
                        <button type="button" role="radio" data-setting="font" data-value="atkinson" style="font-family: 'Atkinson Hyperlegible', sans-serif">Atkinson</button>
                        <button type="button" role="radio" data-setting="font" data-value="sans" style="font-family: Figtree, system-ui, sans-serif">Sans</button>
                    </div>
                </div>

                <div class="reader-setting" data-book-only>
                    <p class="reader-setting__label">Text size <span data-size-label></span></p>
                    <div class="reader-stepper">
                        <button type="button" data-step="size" data-delta="-1" aria-label="Smaller text"><span style="font-size: .8rem">A</span></button>
                        <input type="range" min="14" max="30" step="1" data-setting-range="size" aria-label="Text size">
                        <button type="button" data-step="size" data-delta="1" aria-label="Larger text"><span style="font-size: 1.25rem">A</span></button>
                    </div>
                </div>

                <div class="reader-setting" data-pdf-only hidden>
                    <p class="reader-setting__label">Zoom <span data-zoom-label>Fit</span></p>
                    <div class="reader-stepper">
                        <button type="button" data-zoom-out aria-label="Zoom out">−</button>
                        <button type="button" data-zoom-fit class="reader-stepper__wide">Fit page</button>
                        <button type="button" data-zoom-in aria-label="Zoom in">+</button>
                    </div>
                </div>

                <div class="reader-setting" data-book-only>
                    <p class="reader-setting__label">Line spacing</p>
                    <div class="reader-choice" role="radiogroup" aria-label="Line spacing">
                        <button type="button" role="radio" data-setting="leading" data-value="compact">Compact</button>
                        <button type="button" role="radio" data-setting="leading" data-value="normal">Normal</button>
                        <button type="button" role="radio" data-setting="leading" data-value="relaxed">Relaxed</button>
                    </div>
                </div>

                <div class="reader-setting" data-book-only>
                    <p class="reader-setting__label">Margins</p>
                    <div class="reader-choice" role="radiogroup" aria-label="Margins">
                        <button type="button" role="radio" data-setting="margins" data-value="narrow">Narrow</button>
                        <button type="button" role="radio" data-setting="margins" data-value="normal">Normal</button>
                        <button type="button" role="radio" data-setting="margins" data-value="wide">Wide</button>
                    </div>
                </div>

                <div class="reader-setting" data-book-only>
                    <p class="reader-setting__label">Alignment</p>
                    <div class="reader-choice" role="radiogroup" aria-label="Alignment">
                        <button type="button" role="radio" data-setting="align" data-value="justify">Justified</button>
                        <button type="button" role="radio" data-setting="align" data-value="left">Left</button>
                    </div>
                </div>

                <div class="reader-setting">
                    <p class="reader-setting__label">Pages</p>
                    <div class="reader-choice" role="radiogroup" aria-label="Pages">
                        <button type="button" role="radio" data-setting="spread" data-value="auto">Two when wide</button>
                        <button type="button" role="radio" data-setting="spread" data-value="single">One page</button>
                    </div>
                </div>

                <div class="reader-setting">
                    <p class="reader-setting__label">Theme</p>
                    <div class="reader-themes" role="radiogroup" aria-label="Theme">
                        <button type="button" role="radio" data-setting="theme" data-value="paper" class="reader-swatch reader-swatch--paper">Paper</button>
                        <button type="button" role="radio" data-setting="theme" data-value="sepia" class="reader-swatch reader-swatch--sepia">Sepia</button>
                        <button type="button" role="radio" data-setting="theme" data-value="sage" class="reader-swatch reader-swatch--sage">Sage</button>
                        <button type="button" role="radio" data-setting="theme" data-value="night" class="reader-swatch reader-swatch--night">Night</button>
                    </div>
                </div>

                <button type="button" class="reader-link" data-reset-settings>Reset to defaults</button>
            </section>

            {{-- Saved highlights --}}
            <aside id="reader-highlights" class="reader-panel reader-panel--highlights" data-panel="highlights" hidden aria-labelledby="saved-highlights-heading">
                <div class="reader-panel__head">
                    <h2 id="saved-highlights-heading">Saved highlights</h2>
                    <button type="button" class="reader-btn reader-btn--icon" data-close-panel aria-label="Close highlights">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
                    </button>
                </div>
                <p class="reader-panel__hint">Select text on the page to highlight, quote, copy or share it. Your highlights are private to you.</p>
                <p data-highlights-empty class="reader-empty">No highlights yet.</p>
                <ul data-highlights-list class="reader-hl-list"></ul>
            </aside>

            {{-- Floating toolbar for a selection or an existing highlight --}}
            <div class="reader-pop" data-selection-toolbar role="toolbar" aria-label="Selected text" hidden>
                <button type="button" data-action="highlight"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 11l-5 5v4h4l5-5M14 6l4 4M12 8l6-6 4 4-6 6z"/></svg><span>Highlight</span></button>
                <button type="button" data-action="quote"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 7h4v4c0 3-1.5 5-4 6M15 7h4v4c0 3-1.5 5-4 6"/></svg><span data-quote-label>Quote</span></button>
                <button type="button" data-action="copy"><svg viewBox="0 0 24 24" aria-hidden="true"><rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V5a1 1 0 00-1-1H5a1 1 0 00-1 1v10a1 1 0 001 1h3"/></svg><span>Copy</span></button>
                {{-- Share: placeholder only, wired up later. --}}
                <button type="button" data-action="share" data-share-highlight title="Sharing is coming soon"><svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="18" cy="5" r="2.5"/><circle cx="6" cy="12" r="2.5"/><circle cx="18" cy="19" r="2.5"/><path d="M8.2 10.8l7.6-4.4M8.2 13.2l7.6 4.4"/></svg><span>Share</span></button>
                <button type="button" data-action="remove" hidden><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg><span>Remove</span></button>
            </div>
        </div>
    </div>
</x-app-layout>
