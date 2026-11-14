{{-- Open-book popup shown when a book on the tree is tapped. Filled in by resources/js/tree-shelf.js --}}
<dialog id="book-popup" class="book-popup" aria-labelledby="book-popup-title" data-mode="{{ $isOwnProfile ? 'edit' : 'view' }}">
    <form method="dialog" class="book-popup__book" data-popup-form novalidate>
        <button type="submit" class="book-popup__close" aria-label="Close book" data-popup-close>
            <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M5.3 5.3a1 1 0 0 1 1.4 0L10 8.6l3.3-3.3a1 1 0 1 1 1.4 1.4L11.4 10l3.3 3.3a1 1 0 0 1-1.4 1.4L10 11.4l-3.3 3.3a1 1 0 0 1-1.4-1.4L8.6 10 5.3 6.7a1 1 0 0 1 0-1.4Z"/></svg>
        </button>

        {{-- LEFT PAGE: the book, your rating and your note --}}
        <section class="book-popup__page book-popup__page--left" aria-label="About this book">
            <div class="flex gap-4">
                <div class="book-popup__cover">
                    <img data-pop-cover alt="" hidden>
                    <span data-pop-cover-fallback class="book-popup__cover-fallback"></span>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="book-popup__eyebrow" data-pop="shelf"></p>
                    <h2 id="book-popup-title" class="mt-1 font-serif text-2xl font-semibold leading-tight text-ink dark:text-[#f4e9e2] sm:text-[1.7rem]" data-pop="title"></h2>
                    <p class="mt-1 text-sm text-walnut dark:text-[#d4c2b9]" data-pop="byline"></p>
                    <a data-pop-link="url" class="mt-3 inline-flex items-center gap-1 text-sm font-semibold text-burgundy underline decoration-gold/60 underline-offset-4 hover:text-burgundy-hover dark:text-[#E9A2AE]">Book page &amp; reviews <span aria-hidden="true">→</span></a>
                </div>
            </div>

            <div class="book-popup__rule" aria-hidden="true"><span>❧</span></div>

            <fieldset>
                <legend class="book-popup__label">{{ $isOwnProfile ? 'Your rating' : 'Their rating' }}</legend>
                <div class="mt-2 flex items-center gap-3">
                    <div class="popup-stars" data-pop-stars role="radiogroup" aria-label="Rating">
                        @for ($i = 1; $i <= 5; $i++)
                            <span class="popup-star" data-star="{{ $i }}">
                                <svg viewBox="0 0 20 20" aria-hidden="true"><path class="popup-star__bg" d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.07 3.29a1 1 0 0 0 .95.69h3.46c.97 0 1.37 1.24.59 1.81l-2.8 2.03a1 1 0 0 0-.36 1.12l1.07 3.29c.3.92-.76 1.69-1.54 1.12l-2.8-2.03a1 1 0 0 0-1.18 0l-2.8 2.03c-.78.57-1.84-.2-1.54-1.12l1.07-3.29a1 1 0 0 0-.36-1.12L2.98 8.72c-.78-.57-.38-1.81.59-1.81h3.46a1 1 0 0 0 .95-.69l1.07-3.29Z"/></svg>
                                <svg viewBox="0 0 20 20" class="popup-star__fill" aria-hidden="true"><path d="M9.05 2.93c.3-.92 1.6-.92 1.9 0l1.07 3.29a1 1 0 0 0 .95.69h3.46c.97 0 1.37 1.24.59 1.81l-2.8 2.03a1 1 0 0 0-.36 1.12l1.07 3.29c.3.92-.76 1.69-1.54 1.12l-2.8-2.03a1 1 0 0 0-1.18 0l-2.8 2.03c-.78.57-1.84-.2-1.54-1.12l1.07-3.29a1 1 0 0 0-.36-1.12L2.98 8.72c-.78-.57-.38-1.81.59-1.81h3.46a1 1 0 0 0 .95-.69l1.07-3.29Z"/></svg>
                                @if ($isOwnProfile)
                                    <button type="button" data-rate="{{ $i - 0.5 }}" aria-label="{{ $i - 0.5 }} stars"></button>
                                    <button type="button" data-rate="{{ $i }}" aria-label="{{ $i }} {{ Str::plural('star', $i) }}"></button>
                                @endif
                            </span>
                        @endfor
                    </div>
                    <span class="text-sm font-semibold text-tan dark:text-[#c7a491]" data-pop="rating-text" aria-live="polite"></span>
                    @if ($isOwnProfile)
                        <button type="button" class="ml-auto text-xs font-semibold text-tan underline underline-offset-2 hover:text-burgundy dark:text-[#c7a491]" data-clear-rating>Clear</button>
                    @endif
                </div>
            </fieldset>

            @if ($isOwnProfile)
                <div class="mt-5">
                    <label for="popup-note" class="book-popup__label">Your note <span class="font-normal normal-case tracking-normal text-tan/80">· only you can see this</span></label>
                    <textarea id="popup-note" name="note" rows="5" maxlength="3000" class="book-popup__note" placeholder="What stayed with you? A favorite line, a feeling, who you’d lend it to…"></textarea>
                </div>
            @else
                <blockquote class="mt-5 font-serif text-lg italic leading-8 text-walnut dark:text-[#e3d3ca]" data-pop="review"></blockquote>
            @endif

            <p class="book-popup__folio" aria-hidden="true">— i —</p>
        </section>

        <div class="book-popup__spine" aria-hidden="true"></div>

        {{-- RIGHT PAGE: reading status and the bookmark --}}
        <section class="book-popup__page book-popup__page--right" aria-label="Your reading">
            @if ($isOwnProfile)
                <fieldset>
                    <legend class="book-popup__label">Reading status</legend>
                    <div class="popup-status mt-2">
                        <label><input type="radio" name="status" value="want_to_read"><span><b>📚</b>Want to read</span></label>
                        <label><input type="radio" name="status" value="reading"><span><b>📖</b>Reading</span></label>
                        <label><input type="radio" name="status" value="read"><span><b>✓</b>Finished</span></label>
                    </div>
                </fieldset>

                <div class="popup-bookmark mt-6">
                    <div class="flex items-start justify-between gap-3">
                        <div>
                            <p class="book-popup__label">Where’s my bookmark?</p>
                            <p class="mt-1 font-serif text-xl font-semibold text-ink dark:text-[#f4e9e2]" data-pop="bookmark-text"></p>
                        </div>
                        <span class="popup-bookmark__ribbon" aria-hidden="true"></span>
                    </div>
                    <div class="mt-3 flex items-center gap-2 text-sm text-walnut dark:text-[#d4c2b9]">
                        <label for="popup-current-page">Page</label>
                        <input id="popup-current-page" name="current_page" type="number" min="0" inputmode="numeric" class="bw-input w-24 py-1.5 text-center">
                        <label for="popup-total-pages">of</label>
                        <input id="popup-total-pages" name="total_pages" type="number" min="1" inputmode="numeric" class="bw-input w-24 py-1.5 text-center" placeholder="?">
                    </div>
                    <label for="popup-page-slider" class="sr-only">Move bookmark</label>
                    <input id="popup-page-slider" type="range" min="0" max="100" value="0" class="popup-bookmark__slider mt-4 w-full" data-page-slider>
                    <div class="mt-1 flex justify-between text-[11px] font-semibold uppercase tracking-wider text-tan dark:text-[#c7a491]">
                        <span>First page</span><span data-pop="percent"></span><span>The end</span>
                    </div>
                    <p class="mt-2 text-xs text-[#C24A4A]" data-pop-error hidden></p>
                </div>

                <dl class="popup-dates mt-5">
                    <div><dt>Shelved</dt><dd data-pop="added_at"></dd></div>
                    <div><dt>Started</dt><dd data-pop="started_at"></dd></div>
                    <div><dt>Finished</dt><dd data-pop="read_at"></dd></div>
                    <div><dt>Bookmark moved</dt><dd data-pop="bookmark_moved"></dd></div>
                </dl>

                <div class="mt-5 flex flex-wrap items-center gap-2">
                    <a data-pop-link="pdf_url" class="btn-ghost text-sm" hidden>Continue reading PDF</a>
                    <label for="popup-move" class="sr-only">Move to branch</label>
                    <select id="popup-move" class="bw-input py-1.5 text-sm" data-pop-move>
                        @foreach ($shelfOptions as $option)
                            <option value="{{ $option['slot'] }}">Branch: {{ $option['title'] }}</option>
                        @endforeach
                    </select>
                    <button type="button" class="ml-auto text-sm font-semibold text-[#B03A48] underline underline-offset-2 dark:text-[#f0a5ae]" data-pop-remove>Remove</button>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3 border-t border-dashed border-warmborder pt-4 dark:border-[#4B3035]">
                    <span class="text-sm text-tan dark:text-[#c7a491]" data-pop-saved aria-live="polite"></span>
                    <button type="button" class="btn-primary" data-pop-save>Save</button>
                </div>
            @else
                <p class="book-popup__label">Status</p>
                <p class="mt-1 font-serif text-2xl font-semibold text-ink dark:text-[#f4e9e2]" data-pop="status_label"></p>
                <dl class="popup-dates mt-5">
                    <div><dt>Finished</dt><dd data-pop="read_at"></dd></div>
                </dl>
                <p class="mt-6 text-sm text-walnut dark:text-[#d4c2b9]">Curious? Open the book page to read every reader’s thoughts.</p>
            @endif
            <p class="book-popup__folio" aria-hidden="true">— ii —</p>
        </section>
    </form>
</dialog>
