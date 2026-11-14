<x-app-layout>
    @push('scripts')
        @vite(['resources/css/reader.css', 'resources/js/pdf-reader.js'])
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
              data-book-id="{{ $book->id }}"
              data-barnaby-explain-url="{{ route('barnaby.explain') }}"
              data-barnaby-recap-url="{{ route('barnaby.recap', $book) }}"
              data-book-author="{{ $book->author_name }}"
              data-post-url="{{ route('posts.store') }}"
              data-delete-highlight-url="{{ route('books.pdfs.highlights.index', [$book, $bookPdf], false) }}/__HIGHLIGHT__">

            {{-- Top bar --}}
            <header class="reader-bar">
                <a href="{{ route('books.show', $book) }}" class="reader-btn reader-btn--icon" aria-label="Back to {{ $book->title }}" title="Back to book">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
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
                <button type="button" class="reader-btn reader-btn--icon" data-barnaby-recap aria-controls="reader-barnaby" aria-label="Story so far — ask Barnaby for a recap" title="Story so far (Barnaby)">
                    <x-barnaby-avatar class="h-7 w-7" data-barnaby-avatar />
                </button>
                <button type="button" class="reader-btn reader-btn--icon" data-toggle-panel="highlights" aria-expanded="false" aria-controls="reader-highlights" aria-label="Saved highlights" title="Saved highlights">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" aria-hidden="true"><path d="M6 3h12v18l-6-4-6 4z"/></svg>
                    <span class="reader-badge" data-highlight-count hidden>0</span>
                </button>
                <button type="button" class="reader-btn reader-btn--icon" data-fullscreen aria-label="Full screen" title="Full screen">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" aria-hidden="true"><path d="M4 9V4h5M20 9V4h-5M4 15v5h5M20 15v5h-5"/></svg>
                </button>
            </header>

            {{-- Book / PDF stage --}}
            <div class="reader-stage" data-stage>
                <button type="button" class="reader-turn reader-turn--prev" data-previous-page disabled aria-label="Previous page">
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
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
                    <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg>
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
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
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

                @php
                    // name => [label, desk, page, ink, tone]
                    $readerThemes = [
                        'paper' => ['Paper', '#efe6da', '#fffdf8', '#2b1f1d', 'light'],
                        'sepia' => ['Sepia', '#e6d7bb', '#f6ecd6', '#4a3523', 'light'],
                        'parchment' => ['Old book', 'linear-gradient(135deg,#3b2418,#5a3a2e)', 'radial-gradient(ellipse at center,#f8eed6 50%,#ead6ac)', '#3d2a17', 'light'],
                        'linen' => ['Linen', '#e9e6df', '#fbfaf7', '#2d3138', 'light'],
                        'sage' => ['Sage', '#d5e0d2', '#eef3ea', '#24332b', 'light'],
                        'rose' => ['Rose', '#f0d9dd', '#fff7f7', '#4a2230', 'light'],
                        'mist' => ['Mist', '#d6e2ea', '#f4f8fb', '#1f3447', 'light'],
                        'contrast' => ['High contrast', '#ffffff', '#ffffff', '#000000', 'light'],
                        'night' => ['Night', '#110c0d', '#1e1718', '#e8dcd3', 'dark'],
                        'midnight' => ['Midnight', 'radial-gradient(circle at 70% 0%,#1d2a57,#0b1024)', '#121a33', '#d6b778', 'dark'],
                        'dusk' => ['Dusk', '#1b1424', '#241a30', '#e8dcef', 'dark'],
                        'forest' => ['Forest', '#0e1a14', '#152820', '#e4ecdf', 'dark'],
                        'candle' => ['Candlelight', 'radial-gradient(circle at 50% 0%,#3d2512,#140b06)', '#1f140c', '#f0c98f', 'dark'],
                        'black' => ['Black', '#000000', '#000000', '#d6d6d6', 'dark'],
                    ];
                @endphp
                <div class="reader-setting">
                    @foreach (['light' => 'Light themes', 'dark' => 'Dark themes'] as $tone => $heading)
                        <p class="reader-setting__label">{{ $heading }}</p>
                        <div class="reader-themes" role="radiogroup" aria-label="{{ $heading }}">
                            @foreach ($readerThemes as $value => [$label, $desk, $page, $ink, $themeTone])
                                @continue($themeTone !== $tone)
                                <button type="button" role="radio" class="reader-theme" data-setting="theme" data-value="{{ $value }}" data-tone="{{ $themeTone }}">
                                    <span class="reader-theme__sample" style="background: {{ $desk }}" aria-hidden="true"><span style="background: {{ $page }}; color: {{ $ink }}">Aa</span></span>
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                    @endforeach
                </div>

                <button type="button" class="reader-link" data-reset-settings>Reset to defaults</button>
            </section>

            {{-- Saved highlights --}}
            {{-- Barnaby: help with a passage, or the story so far --}}
            <aside id="reader-barnaby" class="reader-panel reader-panel--barnaby" data-panel="barnaby" hidden aria-labelledby="reader-barnaby-heading">
                <div class="reader-panel__head">
                    <h2 id="reader-barnaby-heading" class="flex items-center gap-2"><x-barnaby-avatar class="h-8 w-8" /> <span data-barnaby-reader-title>Barnaby</span></h2>
                    <button type="button" class="reader-btn reader-btn--icon" data-close-panel aria-label="Close Barnaby">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
                    </button>
                </div>
                <div data-barnaby-reader-body></div>
                <p class="reader-panel__hint mt-3">Barnaby uses what he knows about this book and never goes past your page. He can be wrong — trust the book!</p>
            </aside>

            <aside id="reader-highlights" class="reader-panel reader-panel--highlights" data-panel="highlights" hidden aria-labelledby="saved-highlights-heading">
                <div class="reader-panel__head">
                    <h2 id="saved-highlights-heading">Saved highlights</h2>
                    <button type="button" class="reader-btn reader-btn--icon" data-close-panel aria-label="Close highlights">
                        <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
                    </button>
                </div>
                <p class="reader-panel__hint">Select text on the page to highlight, quote, copy or share it. Your highlights are private to you.</p>
                <p data-highlights-empty class="reader-empty">No highlights yet.</p>
                <ul data-highlights-list class="reader-hl-list"></ul>
            </aside>

            {{-- Floating toolbar for a selection or an existing highlight --}}
            <div class="reader-pop" data-selection-toolbar role="toolbar" aria-label="Selected text" hidden>
                <button type="button" data-action="highlight"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" aria-hidden="true"><path d="M9 11l-5 5v4h4l5-5M14 6l4 4M12 8l6-6 4 4-6 6z"/></svg><span>Highlight</span></button>
                <button type="button" data-action="quote"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" aria-hidden="true"><path d="M7 7h4v4c0 3-1.5 5-4 6M15 7h4v4c0 3-1.5 5-4 6"/></svg><span data-quote-label>Quote</span></button>
                <button type="button" data-action="barnaby"><x-barnaby-avatar width="20" height="20" /><span>Ask Barnaby</span></button>
                <button type="button" data-action="copy"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" aria-hidden="true"><rect x="8" y="8" width="12" height="12" rx="2"/><path d="M16 8V5a1 1 0 00-1-1H5a1 1 0 00-1 1v10a1 1 0 001 1h3"/></svg><span>Copy</span></button>
                <button type="button" data-action="share"><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" aria-hidden="true"><circle cx="18" cy="5" r="2.5"/><circle cx="6" cy="12" r="2.5"/><circle cx="18" cy="19" r="2.5"/><path d="M8.2 10.8l7.6-4.4M8.2 13.2l7.6 4.4"/></svg><span>Share</span></button>
                <button type="button" data-action="remove" hidden><svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" aria-hidden="true"><path d="M4 7h16M10 11v6M14 11v6M6 7l1 13h10l1-13M9 7V4h6v3"/></svg><span>Remove</span></button>
            </div>

            {{-- Share a quote to the feed --}}
            <div class="reader-share" data-share-sheet hidden>
                <div class="reader-share__backdrop" data-share-close aria-hidden="true"></div>
                <section class="reader-share__panel" role="dialog" aria-modal="true" aria-labelledby="reader-share-title">
                    <header class="reader-share__head">
                        <h2 id="reader-share-title">Share this quote</h2>
                        <button type="button" class="reader-btn reader-btn--icon" data-share-close aria-label="Close">
                            <svg viewBox="0 0 24 24" width="20" height="20" fill="none" stroke="currentColor" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
                        </button>
                    </header>
                    <div class="reader-share__body">
                        <div class="reader-share__preview" data-share-preview></div>
                        <div class="reader-share__controls" data-share-form>
                            <div>
                                <p class="reader-setting__label">Design</p>
                                <div class="reader-share__swatches" role="radiogroup" aria-label="Card design" data-share-designs></div>
                            </div>
                            <div>
                                <p class="reader-setting__label">Typeface</p>
                                <div class="reader-share__fonts" role="radiogroup" aria-label="Typeface" data-share-fonts></div>
                            </div>
                            <div>
                                <label class="reader-setting__label" for="reader-share-caption">Caption</label>
                                <textarea id="reader-share-caption" data-share-caption rows="3" maxlength="2000" placeholder="Why did this line stay with you? (optional)"></textarea>
                            </div>
                            <div class="reader-share__actions">
                                <button type="button" class="reader-share__primary" data-share-post>Post to feed</button>
                                <button type="button" class="reader-share__secondary" data-share-copy>Copy text</button>
                            </div>
                        </div>
                        <div class="reader-share__done" data-share-done hidden>
                            <p class="reader-share__done-title">It’s on the feed.</p>
                            <p class="reader-share__done-text">Your reading circle can now like and reply to it.</p>
                            <div class="reader-share__actions">
                                <a class="reader-share__primary" data-share-view href="#">View post</a>
                                <button type="button" class="reader-share__secondary" data-share-link>Copy link</button>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </div>
</x-app-layout>
