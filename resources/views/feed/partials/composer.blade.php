{{--
    "Create new post" flow, Instagram style.
      Photo:      choose → select photos → frame & filter → caption → share
      Quote card: choose → write → design & caption → share
      Thought:    choose → write → design & caption → share
    Logic: Alpine.data('composer') in resources/js/app.js. Expects $quoteCards and $shelfBooks.
--}}
@php
    $composerCards = $quoteCards->map(fn ($card) => [
        'id' => $card->id,
        'text' => $card->highlight->text,
        'title' => $card->highlight->bookPdf->book->title ?? '',
        'author' => $card->highlight->bookPdf->book->author_name ?? '',
        'page' => $card->highlight->page_number,
    ])->values();
    $composerBooks = $shelfBooks->map(fn ($book) => ['id' => $book->id, 'title' => $book->title, 'author' => $book->author_name])->values();
    $composerOld = $errors->any() ? [
        'kind' => old('type') === 'quote' ? 'quote' : (old('photo_ratio') ? 'photo' : 'thought'),
        'quoteText' => old('quote_text', ''),
        'cardId' => old('quote_card_id') ? (int) old('quote_card_id') : null,
        'bookId' => old('book_id') ? (int) old('book_id') : null,
        'page' => old('quote_page', ''),
        'body' => old('body', ''),
        'design' => old('card_style'),
        'font' => old('card_font'),
    ] : null;
    $photoFilters = ['normal' => 'Normal', 'warm' => 'Warm', 'cool' => 'Cool', 'vintage' => 'Vintage', 'fade' => 'Fade', 'golden' => 'Golden', 'rose' => 'Rosé', 'vivid' => 'Vivid', 'mono' => 'Mono', 'noir' => 'Noir'];
@endphp
<div x-data="composer(@js(['cards' => $composerCards, 'books' => $composerBooks, 'old' => $composerOld, 'maxPhotos' => \App\Models\Post::MAX_PHOTOS]))"
     x-on:open-composer.window="open($event.detail)"
     x-on:keydown.escape.window="close()"
     x-cloak>
    <div x-show="isOpen" x-transition.opacity class="fixed inset-0 z-[70] bg-black/60 backdrop-blur-sm" @click="close()" aria-hidden="true"></div>

    <section x-show="isOpen" x-transition.scale.95 role="dialog" aria-modal="true" aria-labelledby="composer-title"
             class="fixed inset-x-2 bottom-2 top-16 z-[71] mx-auto flex flex-col overflow-hidden rounded-3xl bg-paper shadow-2xl dark:bg-raised sm:inset-x-6 sm:bottom-8 sm:top-24"
             :class="wide() ? 'max-w-4xl' : 'max-w-xl'">
        <form method="POST" action="{{ route('posts.store') }}" enctype="multipart/form-data" class="flex min-h-0 flex-1 flex-col" @submit="prepareSubmit($event)" x-ref="form">
            @csrf
            <input type="hidden" name="type" :value="kind === 'quote' ? 'quote' : 'post'">
            <input type="hidden" name="quote_card_id" :value="kind === 'quote' && source === 'saved' ? cardId : ''" :disabled="!(kind === 'quote' && source === 'saved')">
            <input type="hidden" name="quote_text" :value="quoteText" :disabled="!(kind === 'quote' && source === 'typed')">
            <input type="hidden" name="quote_page" :value="page" :disabled="!(kind === 'quote' && source === 'typed') || !page">
            <input type="hidden" name="book_id" :value="bookId ?? ''" :disabled="!bookId || (kind === 'quote' && source === 'saved')">
            <input type="hidden" name="body" :value="kind === 'thought' ? thought : caption">
            <input type="hidden" name="card_style" :value="design" :disabled="kind === 'photo' || (kind === 'thought' && !onCard)">
            <input type="hidden" name="card_font" :value="font" :disabled="kind === 'photo' || (kind === 'thought' && !onCard)">
            <input type="hidden" name="photo_ratio" :value="ratio" :disabled="kind !== 'photo'">
            <template x-for="(photo, index) in photos" :key="photo.id">
                <span>
                    <input type="hidden" :name="`photo_meta[${index}][focus_x]`" :value="Math.round(photo.focusX)" :disabled="kind !== 'photo'">
                    <input type="hidden" :name="`photo_meta[${index}][focus_y]`" :value="Math.round(photo.focusY)" :disabled="kind !== 'photo'">
                    <input type="hidden" :name="`photo_meta[${index}][filter]`" :value="photo.filter" :disabled="kind !== 'photo'">
                </span>
            </template>
            {{-- Filled with the resized photos just before the form is sent. --}}
            <input type="file" name="photos[]" multiple class="hidden" x-ref="upload" tabindex="-1" aria-hidden="true">
            {{-- The picker the "Select" and "+" buttons open (not sent; photos are resized first). --}}
            <input type="file" accept="image/*" multiple class="sr-only" x-ref="picker" id="composer-photos" tabindex="-1" @change="addFiles($event.target.files); $event.target.value = ''">

            {{-- Header --}}
            <header class="flex items-center gap-2 border-b border-warmborder px-3 py-2.5 dark:border-[#4B3035]">
                <button type="button" class="ig-icon !h-10 !w-10" @click="back()" :aria-label="stage === 'choose' ? 'Close' : 'Back'">
                    <svg x-show="stage !== 'choose'" viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
                    <svg x-show="stage === 'choose'" viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
                <h2 id="composer-title" class="flex-1 text-center font-semibold text-ink dark:text-[#F4E9E2]" x-text="title()"></h2>
                <button type="button" x-show="!isLast() && stage !== 'choose'" @click="next()" :disabled="!canContinue()" class="min-h-10 rounded-full px-3 text-sm font-semibold text-burgundy disabled:opacity-40 dark:text-[#E9A2AE]">Next</button>
                <button type="submit" x-show="isLast()" :disabled="submitting || processing" class="min-h-10 rounded-full bg-burgundy px-4 text-sm font-semibold text-[#FFF8EF] disabled:opacity-50 dark:bg-burgundy-dark" x-text="submitting ? 'Sharing…' : 'Share'">Share</button>
                <span x-show="stage === 'choose'" class="w-10" aria-hidden="true"></span>
            </header>

            @if ($errors->any())
                <div class="border-b border-red-200 bg-red-50 px-4 py-2 text-sm text-red-800 dark:border-red-900 dark:bg-red-950/40 dark:text-red-200" role="alert">
                    {{ $errors->first() }}
                </div>
            @endif

            {{-- Choose --}}
            <div x-show="stage === 'choose'" class="grid flex-1 content-center gap-3 overflow-y-auto p-5 sm:grid-cols-3">
                <button type="button" @click="choose('photo')" class="focus-ring rounded-3xl border border-warmborder p-4 text-left transition hover:-translate-y-0.5 hover:border-burgundy/50 hover:shadow-lg dark:border-[#4B3035]">
                    <div class="mx-auto grid aspect-[4/5] w-32 place-items-center rounded-xl bg-gradient-to-br from-[#f58f7c] via-[#c2547a] to-[#6c3b85] text-white shadow-md">
                        <svg class="h-12 w-12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="4"/><circle cx="8.5" cy="9" r="1.8"/><path d="m21 15-4.5-4.5L6 21"/></svg>
                    </div>
                    <p class="mt-3 font-serif text-lg font-semibold text-ink dark:text-[#F4E9E2]">Photo</p>
                    <p class="mt-0.5 text-xs text-tan">Your shelf, your reading nook, a page — up to {{ \App\Models\Post::MAX_PHOTOS }} photos.</p>
                </button>
                <button type="button" @click="choose('quote')" class="focus-ring rounded-3xl border border-warmborder p-4 text-left transition hover:-translate-y-0.5 hover:border-burgundy/50 hover:shadow-lg dark:border-[#4B3035]">
                    <div class="mx-auto w-32 overflow-hidden rounded-xl shadow-md">
                        <x-quote-card text="So it goes." design="wine" font="serif" title="A favourite book" meta="p. 42" />
                    </div>
                    <p class="mt-3 font-serif text-lg font-semibold text-ink dark:text-[#F4E9E2]">Quote card</p>
                    <p class="mt-0.5 text-xs text-tan">Type a line or pick a highlight, then design its card.</p>
                </button>
                <button type="button" @click="choose('thought')" class="focus-ring rounded-3xl border border-warmborder p-4 text-left transition hover:-translate-y-0.5 hover:border-burgundy/50 hover:shadow-lg dark:border-[#4B3035]">
                    <div class="mx-auto w-32 overflow-hidden rounded-xl shadow-md">
                        <x-quote-card text="Stayed up way too late finishing this one." design="sunset" font="sans" :thought="true" />
                    </div>
                    <p class="mt-3 font-serif text-lg font-semibold text-ink dark:text-[#F4E9E2]">Thought</p>
                    <p class="mt-0.5 text-xs text-tan">What you’re reading or feeling — as text, or on a card.</p>
                </button>
            </div>

            {{-- Photo: select --}}
            <div x-show="stage === 'select'" class="flex flex-1 flex-col overflow-y-auto p-5">
                <label for="composer-photos" class="ph-drop flex-1 cursor-pointer"
                       :class="dragOver && 'is-over'"
                       @dragover.prevent="dragOver = true" @dragleave.prevent="dragOver = false"
                       @drop.prevent="dragOver = false; addFiles($event.dataTransfer.files)">
                    <svg class="h-16 w-16 text-tan" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.3" aria-hidden="true"><rect x="3" y="3" width="18" height="18" rx="4"/><circle cx="8.5" cy="9" r="1.8"/><path d="m21 15-4.5-4.5L6 21"/></svg>
                    <span class="font-serif text-xl text-ink dark:text-[#F4E9E2]">Drag photos here</span>
                    <span class="btn-primary pointer-events-none">Select from your device</span>
                    <span class="text-xs text-tan">JPG, PNG, WebP or GIF · up to {{ \App\Models\Post::MAX_PHOTOS }} photos</span>
                </label>
                <p x-show="processing" class="mt-3 text-center text-sm text-tan">Preparing your photos…</p>
            </div>

            {{-- Photo: frame & filter --}}
            <div x-show="stage === 'edit'" class="grid min-h-0 flex-1 overflow-y-auto md:grid-cols-[minmax(0,1fr)_19rem] md:overflow-hidden">
                <div class="flex flex-col items-center justify-center gap-3 bg-parchment/50 p-4 dark:bg-black/20 md:overflow-y-auto">
                    <template x-if="current()">
                        <div class="ph-frame max-w-md" :class="[`ig-ratio--${ratio}`, dragging && 'is-dragging']"
                             @pointerdown="startDrag($event)" @pointermove="drag($event)" @pointerup="endDrag($event)" @pointercancel="endDrag($event)" x-ref="frame">
                            <img :src="current().url" :class="`pf-${current().filter}`" :style="`object-position: ${current().focusX}% ${current().focusY}%`" alt="Photo preview">
                            <span class="ph-frame__grid" aria-hidden="true"></span>
                        </div>
                    </template>
                    <p class="text-xs text-tan">Drag the photo to choose what’s in the frame.</p>
                    <div class="flex items-center gap-2" role="radiogroup" aria-label="Frame shape">
                        @foreach (['square' => ['Square', '1:1'], 'portrait' => ['Portrait', '4:5'], 'landscape' => ['Landscape', '1.91:1']] as $value => [$label, $shape])
                            <button type="button" role="radio" class="bw-chip !px-3 !py-1.5 text-xs" :class="ratio === '{{ $value }}' && '!border-burgundy !bg-burgundy !text-[#FFF8EF]'" :aria-checked="ratio === '{{ $value }}'" @click="ratio = '{{ $value }}'">{{ $label }} <span class="opacity-70">{{ $shape }}</span></button>
                        @endforeach
                    </div>
                </div>

                <div class="space-y-5 border-t border-warmborder p-5 dark:border-[#4B3035] md:overflow-y-auto md:border-l md:border-t-0">
                    <div>
                        <p class="eyebrow">Photos (<span x-text="photos.length"></span>/<span x-text="maxPhotos"></span>)</p>
                        <div class="mt-2 flex gap-2 overflow-x-auto p-1">
                            <template x-for="(photo, index) in photos" :key="photo.id">
                                <div class="relative">
                                    <button type="button" class="ph-thumb" :aria-current="index === active" @click="active = index" :aria-label="`Photo ${index + 1}`">
                                        <img :src="photo.url" :class="`pf-${photo.filter}`" alt="">
                                    </button>
                                    <button type="button" @click="removePhoto(index)" class="absolute -right-1.5 -top-1.5 flex h-5 w-5 items-center justify-center rounded-full bg-ink text-xs text-white shadow" :aria-label="`Remove photo ${index + 1}`">×</button>
                                </div>
                            </template>
                            <label for="composer-photos" x-show="photos.length < maxPhotos" class="ph-thumb flex cursor-pointer items-center justify-center border border-dashed border-warmborder text-2xl text-tan dark:border-[#4B3035]" aria-label="Add more photos">+</label>
                        </div>
                    </div>

                    <div>
                        <p class="eyebrow">Filter</p>
                        <div class="mt-2 flex gap-2.5 overflow-x-auto pb-1" role="radiogroup" aria-label="Filter">
                            @foreach ($photoFilters as $value => $label)
                                <button type="button" role="radio" class="ph-filter" :aria-checked="current()?.filter === '{{ $value }}'" @click="setFilter('{{ $value }}')">
                                    <img :src="current()?.url" class="pf-{{ $value }}" alt="">
                                    {{ $label }}
                                </button>
                            @endforeach
                        </div>
                        <button type="button" x-show="photos.length > 1" @click="filterAll()" class="mt-2 text-xs font-semibold text-burgundy underline dark:text-[#E9A2AE]">Use this filter on every photo</button>
                    </div>
                </div>
            </div>

            {{-- Photo: caption --}}
            <div x-show="stage === 'caption'" class="flex-1 overflow-y-auto p-5">
                <div class="flex gap-3">
                    <div class="w-24 shrink-0 overflow-hidden rounded-xl" :class="`ig-ratio--${ratio}`">
                        <img x-show="photos[0]" :src="photos[0]?.url" :class="`pf-${photos[0]?.filter}`" :style="`object-position: ${photos[0]?.focusX}% ${photos[0]?.focusY}%`" class="h-full w-full object-cover" alt="">
                    </div>
                    <div class="min-w-0 flex-1">
                        <label for="composer-photo-caption" class="sr-only">Caption</label>
                        <textarea id="composer-photo-caption" x-model="caption" x-ref="photoCaption" rows="6" maxlength="2000" placeholder="Write a caption…" class="bw-input w-full text-[15px] leading-7"></textarea>
                        <p class="mt-1 text-right text-xs text-tan"><span x-text="caption.length"></span>/2000</p>
                    </div>
                </div>
                <label for="composer-photo-book" class="eyebrow mt-4 block">Tag a book</label>
                <select id="composer-photo-book" x-model.number="bookId" class="bw-input mt-2 w-full text-sm">
                    <option value="">📖 Which book is in the picture? (optional)</option>
                    <template x-for="book in books" :key="book.id"><option :value="book.id" x-text="book.title + (book.author ? ' — ' + book.author : '')" :selected="book.id === bookId"></option></template>
                </select>
            </div>

            {{-- Quote / thought: write --}}
            <div x-show="stage === 'write'" class="flex-1 space-y-4 overflow-y-auto p-5">
                <template x-if="kind === 'quote'">
                    <div class="space-y-4">
                        <div class="flex gap-2 text-sm" x-show="cards.length">
                            <button type="button" @click="source = 'typed'" class="bw-chip" :class="source === 'typed' && '!border-burgundy !bg-burgundy !text-[#FFF8EF]'">Type a quote</button>
                            <button type="button" @click="source = 'saved'" class="bw-chip" :class="source === 'saved' && '!border-burgundy !bg-burgundy !text-[#FFF8EF]'">From my highlights (<span x-text="cards.length"></span>)</button>
                        </div>

                        <div x-show="source === 'typed'" class="space-y-3">
                            <label for="composer-quote" class="sr-only">Quote</label>
                            <textarea id="composer-quote" x-model="quoteText" x-ref="quote" rows="5" maxlength="1000" placeholder="“Type the line that stayed with you…”" class="bw-input w-full font-serif text-xl leading-snug"></textarea>
                            <div class="flex flex-wrap gap-2">
                                <label for="composer-quote-book" class="sr-only">Book</label>
                                <select id="composer-quote-book" x-model.number="bookId" class="bw-input min-w-0 flex-1 text-sm">
                                    <option value="">From which book?</option>
                                    <template x-for="book in books" :key="book.id"><option :value="book.id" x-text="book.title + (book.author ? ' — ' + book.author : '')" :selected="book.id === bookId"></option></template>
                                </select>
                                <label for="composer-page" class="sr-only">Page</label>
                                <input id="composer-page" type="number" min="1" x-model="page" placeholder="Page" class="bw-input w-24 text-sm">
                            </div>
                        </div>

                        <ul x-show="source === 'saved'" class="grid gap-2">
                            <template x-for="card in cards" :key="card.id">
                                <li>
                                    <button type="button" @click="cardId = card.id" class="w-full rounded-2xl border p-3 text-left transition" :class="cardId === card.id ? 'border-burgundy bg-rosetint/60 dark:bg-white/10' : 'border-warmborder hover:border-burgundy/40 dark:border-[#4B3035]'" :aria-pressed="cardId === card.id">
                                        <span class="line-clamp-3 font-serif text-base text-ink dark:text-[#F4E9E2]" x-text="'“' + card.text + '”'"></span>
                                        <span class="mt-1 block truncate text-xs text-tan" x-text="card.title + ' · p. ' + card.page"></span>
                                    </button>
                                </li>
                            </template>
                        </ul>
                    </div>
                </template>

                <template x-if="kind === 'thought'">
                    <div>
                        <label for="composer-thought" class="sr-only">Your thought</label>
                        <textarea id="composer-thought" x-model="thought" x-ref="thought" rows="7" maxlength="2000" placeholder="What are you reading, thinking, or loving lately?" class="bw-input w-full text-lg leading-8"></textarea>
                        <p class="mt-1 text-right text-xs text-tan"><span x-text="thought.length"></span>/2000</p>
                    </div>
                </template>
            </div>

            {{-- Quote / thought: design & caption --}}
            <div x-show="stage === 'design'" class="grid min-h-0 flex-1 overflow-y-auto md:grid-cols-[minmax(0,1fr)_20rem] md:overflow-hidden">
                <div class="flex items-center justify-center bg-parchment/50 p-5 dark:bg-black/20 md:overflow-y-auto">
                    <div class="w-full max-w-sm overflow-hidden rounded-2xl shadow-xl" x-show="kind === 'quote' || onCard">
                        <figure class="qcard" :class="cardClasses()">
                            <div class="qcard__inner">
                                <span class="qcard__mark" aria-hidden="true">“</span>
                                <blockquote class="qcard__text" x-text="previewText()"></blockquote>
                                <template x-if="kind === 'quote' && (credit().title || credit().meta)">
                                    <figcaption class="qcard__credit">
                                        <span class="qcard__title" x-text="credit().title" x-show="credit().title"></span>
                                        <span class="qcard__meta" x-text="credit().meta" x-show="credit().meta"></span>
                                    </figcaption>
                                </template>
                            </div>
                            <span class="qcard__brand" aria-hidden="true">Bookworm</span>
                        </figure>
                    </div>
                    <div x-show="kind === 'thought' && !onCard" class="w-full max-w-sm rounded-2xl bg-paper p-5 text-[17px] leading-7 text-ink shadow-xl dark:bg-raised dark:text-[#F4E9E2]">
                        <p class="whitespace-pre-line break-words" x-text="thought"></p>
                    </div>
                </div>

                <div class="space-y-5 border-t border-warmborder p-5 dark:border-[#4B3035] md:overflow-y-auto md:border-l md:border-t-0">
                    <label x-show="kind === 'thought'" class="flex items-center justify-between gap-3 text-sm font-semibold text-ink dark:text-[#F4E9E2]">
                        Put it on a card
                        <input type="checkbox" x-model="onCard" class="h-5 w-5 rounded border-warmborder text-burgundy focus:ring-burgundy">
                    </label>

                    <div x-show="kind === 'quote' || onCard">
                        <p class="eyebrow">Design</p>
                        <div class="mt-2 flex flex-wrap gap-2.5" role="radiogroup" aria-label="Card design">
                            @foreach (\App\Models\Post::CARD_STYLES as $style)
                                <button type="button" role="radio" class="qcard-swatch focus-ring" :aria-checked="design === '{{ $style }}'" @click="design = '{{ $style }}'" aria-label="{{ ucfirst($style) }}" title="{{ ucfirst($style) }}">
                                    <span class="qcard qcard--{{ $style }}" aria-hidden="true"></span>
                                </button>
                            @endforeach
                        </div>
                    </div>

                    <div x-show="kind === 'quote' || onCard">
                        <p class="eyebrow">Typeface</p>
                        <div class="mt-2 flex flex-wrap gap-2" role="radiogroup" aria-label="Typeface">
                            @foreach (['serif' => ['Serif', 'Lora, Georgia, serif'], 'classic' => ['Classic', '\'EB Garamond\', Georgia, serif'], 'hand' => ['Handwritten', 'Caveat, cursive'], 'sans' => ['Modern', 'Figtree, sans-serif'], 'mono' => ['Typewriter', '\'Courier New\', monospace']] as $value => [$label, $family])
                                <button type="button" role="radio" class="bw-chip" :class="font === '{{ $value }}' && '!border-burgundy !bg-burgundy !text-[#FFF8EF]'" :aria-checked="font === '{{ $value }}'" @click="font = '{{ $value }}'" style="font-family: {{ $family }}">{{ $label }}</button>
                            @endforeach
                        </div>
                    </div>

                    <div x-show="kind === 'quote'">
                        <label for="composer-caption" class="eyebrow">Caption</label>
                        <textarea id="composer-caption" x-model="caption" rows="4" maxlength="2000" placeholder="Why did this line stay with you? (optional)" class="bw-input mt-2 w-full text-sm leading-6"></textarea>
                    </div>

                    <div x-show="kind === 'thought'">
                        <label for="composer-book" class="eyebrow">Book</label>
                        <select id="composer-book" x-model.number="bookId" class="bw-input mt-2 w-full text-sm">
                            <option value="">📖 Attach a book (optional)</option>
                            <template x-for="book in books" :key="book.id"><option :value="book.id" x-text="book.title" :selected="book.id === bookId"></option></template>
                        </select>
                    </div>
                </div>
            </div>
        </form>
    </section>
</div>
