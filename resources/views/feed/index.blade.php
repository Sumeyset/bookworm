<x-app-layout>
    <x-slot name="title">Reading feed · Bookworm</x-slot>
    <x-slot name="header">
        <x-page-header eyebrow="Notes from your reading circle" title="Reading feed" subtitle="Share a thought, pass along a quote, and see what your reading circle is loving." />
    </x-slot>

    <div class="page-section grid gap-8 lg:grid-cols-[minmax(0,1fr)_20rem]">
        <div class="min-w-0 space-y-5">
            {{-- Composer --}}
            <section id="composer" class="bw-card scroll-mt-28 overflow-hidden" aria-label="Create a post"
                     x-data="{ tab: @js(old('type', 'post')), source: @js(old('quote_card_id') ? 'saved' : 'typed') }">
                <div class="flex border-b border-warmborder/70 dark:border-[#4B3035]" role="tablist">
                    <button type="button" role="tab" :aria-selected="tab === 'post'" @click="tab = 'post'" class="bw-tab" :class="tab === 'post' && 'is-active'">✍️ Write a post</button>
                    <button type="button" role="tab" :aria-selected="tab === 'quote'" @click="tab = 'quote'" class="bw-tab" :class="tab === 'quote' && 'is-active'">❝ Share a quote</button>
                </div>
                <form method="POST" action="{{ route('posts.store') }}" class="space-y-3 p-5">
                    @csrf
                    <input type="hidden" name="type" :value="tab">
                    <div class="flex gap-3">
                        <x-avatar :src="auth()->user()->profile_photo_url" :name="auth()->user()->name" size="sm" />
                        <div class="min-w-0 flex-1 space-y-3">
                            <template x-if="tab === 'quote'">
                                <div class="space-y-3">
                                    @if ($quoteCards->isNotEmpty())
                                        <div class="flex gap-2 text-sm">
                                            <button type="button" @click="source = 'typed'" class="bw-chip" :class="source === 'typed' && '!border-burgundy !bg-burgundy !text-[#FFF8EF]'">Type a quote</button>
                                            <button type="button" @click="source = 'saved'" class="bw-chip" :class="source === 'saved' && '!border-burgundy !bg-burgundy !text-[#FFF8EF]'">From my highlights ({{ $quoteCards->count() }})</button>
                                        </div>
                                    @endif
                                    <div x-show="source === 'saved'" class="space-y-2">
                                        <label for="composer-card" class="sr-only">Choose a saved quote</label>
                                        <select id="composer-card" name="quote_card_id" class="bw-input w-full text-sm" :disabled="source !== 'saved'">
                                            <option value="">Choose one of your saved quotes…</option>
                                            @foreach ($quoteCards as $card)
                                                <option value="{{ $card->id }}" @selected(old('quote_card_id') == $card->id)>“{{ Str::limit($card->highlight->text, 70) }}” — {{ $card->highlight->bookPdf->book->title ?? '' }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div x-show="source === 'typed'" class="space-y-2">
                                        <label for="composer-quote" class="sr-only">Quote</label>
                                        <textarea id="composer-quote" name="quote_text" rows="3" maxlength="1000" :disabled="source !== 'typed'" placeholder="“Type the line that stayed with you…”" class="bw-input w-full font-display text-2xl leading-snug">{{ old('quote_text') }}</textarea>
                                        <div class="flex flex-wrap gap-2">
                                            <label for="composer-quote-book" class="sr-only">Book</label>
                                            <select id="composer-quote-book" name="book_id" :disabled="source !== 'typed'" class="bw-input min-w-0 flex-1 text-sm">
                                                <option value="">From which book?</option>
                                                @foreach ($shelfBooks as $book)
                                                    <option value="{{ $book->id }}" @selected(old('book_id') == $book->id)>{{ $book->title }}{{ $book->author_name ? ' — ' . $book->author_name : '' }}</option>
                                                @endforeach
                                            </select>
                                            <label for="composer-page" class="sr-only">Page</label>
                                            <input id="composer-page" type="number" name="quote_page" min="1" :disabled="source !== 'typed'" value="{{ old('quote_page') }}" placeholder="Page" class="bw-input w-24 text-sm">
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <label for="composer-body" class="sr-only">Your post</label>
                            <textarea id="composer-body" name="body" rows="3" maxlength="2000" :placeholder="tab === 'quote' ? 'Add a thought about it (optional)…' : 'What are you reading, thinking, or loving lately?'" class="bw-input w-full text-[15px] leading-7">{{ old('body') }}</textarea>

                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <template x-if="tab === 'post'">
                                    <div class="min-w-0 flex-1">
                                        <label for="composer-book" class="sr-only">Attach a book</label>
                                        <select id="composer-book" name="book_id" class="bw-input w-full max-w-xs text-sm">
                                            <option value="">📖 Attach a book (optional)</option>
                                            @foreach ($shelfBooks as $book)
                                                <option value="{{ $book->id }}" @selected(old('book_id') == $book->id)>{{ $book->title }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </template>
                                <button type="submit" class="btn-primary ml-auto" x-text="tab === 'quote' ? 'Share quote' : 'Post'">Post</button>
                            </div>
                        </div>
                    </div>
                </form>
            </section>

            {{-- Scope tabs --}}
            <nav class="flex items-center gap-2" aria-label="Feed filter">
                @foreach (['following' => 'Following', 'everyone' => 'Everyone'] as $key => $label)
                    <a href="{{ route('feed.index', $key === 'everyone' ? ['scope' => 'everyone'] : []) }}" @if ($scope === $key) aria-current="page" @endif
                       class="{{ $scope === $key ? 'border-burgundy bg-burgundy text-[#FFF8EF] dark:border-burgundy-dark dark:bg-burgundy-dark' : 'border-warmborder text-walnut hover:border-burgundy/40 dark:border-[#4B3035] dark:text-[#e3d3ca]' }} focus-ring rounded-full border px-4 py-2 text-sm font-semibold transition">{{ $label }}</a>
                @endforeach
            </nav>

            @forelse ($items as $item)
                @include('feed.partials.card', ['item' => $item])
            @empty
                <x-empty-state title="Your reading circle is quiet" message="Write the first post above, or follow a few readers to see their reviews and quotes here.">
                    <a href="{{ route('users.index') }}" class="btn-primary">Find readers</a>
                </x-empty-state>
            @endforelse

            @if ($nextBefore)
                <div class="text-center">
                    <a href="{{ route('feed.index', array_filter(['scope' => $scope === 'everyone' ? 'everyone' : null, 'before' => $nextBefore])) }}" class="btn-ghost">Load older posts</a>
                </div>
            @endif
        </div>

        <aside class="space-y-5 lg:sticky lg:top-28 lg:self-start">
            <div class="bw-card p-5">
                <p class="eyebrow">Your circle</p>
                <h2 class="mt-1 font-serif text-2xl font-semibold text-ink dark:text-[#F4E9E2]">Readers you follow</h2>
                @if ($following->isEmpty())
                    <p class="mt-3 text-sm leading-6 text-walnut dark:text-[#d4c2b9]">You’re not following anyone yet.</p>
                @else
                    <ul class="mt-4 space-y-1">
                        @foreach ($following as $reader)
                            <li>
                                <a href="{{ route('users.show', $reader->username) }}" class="focus-ring flex items-center gap-3 rounded-2xl p-2 transition hover:bg-parchment/70 dark:hover:bg-white/5">
                                    <x-avatar :src="$reader->profile_photo_url" :name="$reader->name" size="sm" />
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-semibold text-ink dark:text-[#F4E9E2]">{{ $reader->name }}</span>
                                        <span class="block text-xs text-tan">{{ $reader->reviews_count }} {{ Str::plural('review', $reader->reviews_count) }}</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
                <a href="{{ route('users.index') }}" class="btn-ghost mt-4 w-full">Find more readers</a>
            </div>
            <a href="{{ route('quotes.index') }}" class="group block rounded-3xl bg-gradient-to-br from-[#4A1220] to-[#8B2A3D] p-5 text-[#FFF8EF] shadow-bookplate transition hover:-translate-y-0.5">
                <p class="font-display text-3xl font-bold">The quote wall</p>
                <p class="mt-1 text-sm text-[#F1D9C4]/85">Every line readers have kept and shared, in one place.</p>
                <span class="mt-4 inline-flex rounded-full bg-white/15 px-4 py-2 text-sm font-semibold backdrop-blur transition group-hover:bg-white/25">Browse quotes &rarr;</span>
            </a>
        </aside>
    </div>
</x-app-layout>
