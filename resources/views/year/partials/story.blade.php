{{--
    "Year in Books" story: full-screen slides you tap through, like the
    recaps music apps make. resources/js/year-story.js runs it.
--}}
@php
    [$typeName, $typeEmoji, $typeLine] = $recap['personality'];
    $maxMonth = max(1, $recap['months']->max());
    $novels = round($recap['pages'] / 320, 1);
    $slides = [];
@endphp
<div class="year-story" data-year-story hidden role="dialog" aria-modal="true" aria-label="{{ $firstName }}’s {{ $recap['year'] }} in books">
    <div class="year-story__bars" aria-hidden="true"></div>
    <button type="button" class="year-story__close" data-story-close aria-label="Close story">✕</button>

    <div class="year-story__track">
        <section class="year-story__slide year-story__slide--wine">
            <p class="year-story__kicker">Bookworm · {{ $recap['year'] }}</p>
            <h2 class="year-story__huge">{{ $firstName }}’s year<br>in books</h2>
            <p class="year-story__lead">Ready? Tap to turn the page.</p>
        </section>

        <section class="year-story__slide year-story__slide--gold">
            <p class="year-story__kicker">You finished</p>
            <p class="year-story__number" data-count-to="{{ $recap['books'] }}">0</p>
            <p class="year-story__lead">{{ Str::plural('book', $recap['books']) }}
                @if ($recap['goal']) — {{ $recap['books'] >= $recap['goal'] ? 'goal smashed! 🎉' : min(100, (int) round($recap['books'] / $recap['goal'] * 100)) . '% of your goal of ' . $recap['goal'] }}@endif
            </p>
            @if ($recap['covers']->isNotEmpty())
                <div class="year-story__covers" aria-hidden="true">
                    @foreach ($recap['covers']->take(12) as $i => $book)
                        <img src="{{ $book->cover_src }}" alt="" style="--i: {{ $i }}" loading="lazy">
                    @endforeach
                </div>
            @endif
        </section>

        <section class="year-story__slide year-story__slide--forest">
            <p class="year-story__kicker">You turned</p>
            <p class="year-story__number" data-count-to="{{ $recap['pages'] }}">0</p>
            <p class="year-story__lead">pages{{ $novels >= 1 ? ' — about ' . rtrim(rtrim(number_format($novels, 1), '0'), '.') . ' average novels' : '' }}.</p>
            @if ($recap['longest_book'])
                <p class="year-story__small">Your longest: <b>{{ $recap['longest_book']->book->title }}</b>{{ ($recap['longest_book']->total_pages ?: $recap['longest_book']->book->page_count) ? ', ' . number_format($recap['longest_book']->total_pages ?: $recap['longest_book']->book->page_count) . ' pages' : '' }}</p>
            @endif
        </section>

        <section class="year-story__slide year-story__slide--night">
            <p class="year-story__kicker">You read on</p>
            <p class="year-story__number" data-count-to="{{ $recap['reading_days'] }}">0</p>
            <p class="year-story__lead">{{ Str::plural('day', $recap['reading_days']) }}</p>
            @if ($recap['longest_streak'] > 1)
                <p class="year-story__small">🔥 Longest streak: <b>{{ $recap['longest_streak'] }} days</b> in a row</p>
            @endif
        </section>

        <section class="year-story__slide year-story__slide--rose">
            <p class="year-story__kicker">Month by month</p>
            <div class="year-story__months" aria-label="Books finished each month">
                @foreach ($recap['months'] as $i => $count)
                    <span style="--h: {{ $count ? max(6, $count / $maxMonth * 100) : 2 }}%"><b>{{ $count ?: '' }}</b><i>{{ \Carbon\Carbon::create()->month($i + 1)->format('M')[0] }}</i></span>
                @endforeach
            </div>
            @if ($recap['busiest_month'])
                <p class="year-story__lead">{{ $recap['busiest_month'] }} was your busiest month.</p>
            @endif
        </section>

        @if ($recap['top_authors']->isNotEmpty())
            <section class="year-story__slide year-story__slide--ink">
                <p class="year-story__kicker">Your authors</p>
                <ol class="year-story__list">
                    @foreach ($recap['top_authors'] as $author => $count)
                        <li><span>{{ $loop->iteration }}</span>{{ $author }} <small>{{ $count }} {{ Str::plural('book', $count) }}</small></li>
                    @endforeach
                </ol>
                <p class="year-story__small">{{ $recap['authors_count'] }} different {{ Str::plural('author', $recap['authors_count']) }} in all.</p>
            </section>
        @endif

        @if ($recap['top_rated']->isNotEmpty())
            <section class="year-story__slide year-story__slide--gold">
                <p class="year-story__kicker">Your favourite</p>
                @php $fav = $recap['top_rated']->first(); @endphp
                @if ($fav->book->cover_url)
                    <img class="year-story__fav" src="{{ $fav->book->cover_src }}" alt="">
                @endif
                <h3 class="year-story__title">{{ $fav->book->title }}</h3>
                <p class="year-story__small">{{ $fav->book->author_name }} · {{ rtrim(rtrim(number_format((float) $fav->my_rating, 1), '0'), '.') }}★</p>
            </section>
        @endif

        @if ($recap['quote'])
            <section class="year-story__slide year-story__slide--wine">
                <p class="year-story__kicker">The quote of your year</p>
                <blockquote class="year-story__quote">“{{ Str::limit($recap['quote']->quote_text, 220) }}”</blockquote>
                <p class="year-story__small">— {{ $recap['quote']->book?->title }}</p>
            </section>
        @endif

        @if ($recap['badges']->isNotEmpty())
            <section class="year-story__slide year-story__slide--forest">
                <p class="year-story__kicker">Badges you earned</p>
                <p class="year-story__number" data-count-to="{{ $recap['badges']->count() }}">0</p>
                <p class="year-story__emojis">{{ $recap['badges']->take(12)->pluck('emoji')->implode(' ') }}</p>
            </section>
        @endif

        <section class="year-story__slide year-story__slide--night">
            <p class="year-story__kicker">Your reading type</p>
            <p class="year-story__type-emoji" aria-hidden="true">{{ $typeEmoji }}</p>
            <h3 class="year-story__title">{{ $typeName }}</h3>
            <p class="year-story__lead">{{ $typeLine }}</p>
        </section>

        <section class="year-story__slide year-story__slide--wine year-story__slide--last">
            <p class="year-story__kicker">That was {{ $recap['year'] }}</p>
            <h2 class="year-story__huge">{{ $recap['books'] }} books.<br>{{ number_format($recap['pages']) }} pages.</h2>
            <p class="year-story__lead">Here’s to the next chapter. 📚</p>
            <div class="year-story__actions">
                <button type="button" data-story-share data-url="{{ $shareUrl }}" data-text="My {{ $recap['year'] }} in books: {{ $recap['books'] }} books and {{ number_format($recap['pages']) }} pages on Bookworm 📚">Share</button>
                <button type="button" data-story-card>Download card</button>
                <button type="button" data-story-replay>Watch again</button>
            </div>
        </section>
    </div>

    <button type="button" class="year-story__tap year-story__tap--back" data-story-back aria-label="Previous"></button>
    <button type="button" class="year-story__tap year-story__tap--next" data-story-next aria-label="Next"></button>
</div>
