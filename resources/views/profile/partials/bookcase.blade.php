{{--
    Bookcase styles (not the tree): "library" (carved mahogany bookcase),
    "mantel" (festive shelves over a fireplace), "nook" (white built-ins
    around a window seat) and "styled" (white shelves with decor). Uses the same .tree-shelf /
    .tree-book pieces as the tree, so dragging, the book popup and renaming
    all work the same. Expects $theme, $treeShelves, $treeBookCount,
    $isOwnProfile, $capacity, $shelfOptions.
--}}
@php
    $firstEmptySlot = $treeShelves->first(fn ($s) => $s['items']->isEmpty())['slot'] ?? null;
    $initial = Str::upper(Str::substr($profile->name, 0, 1));
    $shared = compact('firstEmptySlot', 'capacity', 'isOwnProfile', 'initial');
    // Decor that fills each shelf next to the books (picked by shelf number so it never jumps around).
    $nookDecor = [['plant'], ['candle', 'frame'], ['globe'], ['mug'], ['lantern'], ['succulent'], ['frame'], ['candle']];
    $styledCells = [
        ['spines', ['succulent', 'candles']],
        ['stack', ['letter']],
        ['covers', ['cactus']],
        ['stack', ['bowl', 'succulent']],
        ['spines', ['painting', 'shell']],
        ['covers', ['candles']],
    ];
@endphp
<div class="book-tree bookcase bookcase--{{ $theme }}" role="region" aria-label="{{ ['mantel' => 'Fireplace bookshelves', 'nook' => 'Reading nook bookshelves', 'styled' => 'Styled bookshelf'][$theme] ?? 'Library bookcase' }}"
     data-book-tree
     data-editable="{{ $isOwnProfile ? 'true' : 'false' }}"
     data-capacity="{{ $capacity }}"
     data-shelves='@json($shelfOptions)'>
    <div class="bookcase__frame">
        @if ($theme === 'library')
            <div class="bookcase__crown" aria-hidden="true">
                <span class="bookcase__plaque">{{ $treeBookCount }} {{ Str::plural('book', $treeBookCount) }}</span>
            </div>
        @elseif ($theme === 'mantel')
            <div class="bookcase__garland bookcase__garland--top" aria-hidden="true"></div>
            <span class="bookcase__wreath" aria-hidden="true"></span>
        @elseif ($theme === 'nook')
            <span class="nook__chandelier" aria-hidden="true"></span>
        @elseif ($theme === 'styled')
            <div class="styled__lights" aria-hidden="true">
                @foreach (range(1, 7) as $i)<span></span>@endforeach
            </div>
        @endif

        @if ($theme === 'nook')
            {{-- Shelves on both sides of a window seat --}}
            <div class="nook">
                <div class="nook__column">
                    @foreach ($treeShelves->values()->filter(fn ($s, $i) => $i % 2 === 0) as $shelf)
                        @include('profile.partials.bookcase-shelf', $shared + ['shelf' => $shelf, 'decor' => $nookDecor[$shelf['slot'] % count($nookDecor)], 'variant' => 'bookcase__cell--spines'])
                    @endforeach
                </div>
                <div class="nook__middle" aria-hidden="true">
                    <div class="nook__window"><span class="nook__sky"></span></div>
                    <div class="nook__seat">
                        <span class="nook__cushion nook__cushion--1"></span>
                        <span class="nook__cushion nook__cushion--2"></span>
                        <span class="nook__cat"></span>
                        <span class="nook__blanket"></span>
                    </div>
                    <div class="nook__drawers"><span></span><span></span></div>
                </div>
                <div class="nook__column">
                    @foreach ($treeShelves->values()->filter(fn ($s, $i) => $i % 2 === 1) as $shelf)
                        @include('profile.partials.bookcase-shelf', $shared + ['shelf' => $shelf, 'decor' => $nookDecor[($shelf['slot'] + 3) % count($nookDecor)], 'variant' => 'bookcase__cell--spines'])
                    @endforeach
                </div>
                <span class="nook__ladder" aria-hidden="true"></span>
            </div>
            <div class="nook__floor" aria-hidden="true"><span class="nook__count">{{ $treeBookCount }} {{ Str::plural('book', $treeBookCount) }}</span></div>
        @else
            <div class="bookcase__shelves">
                @foreach ($treeShelves as $shelf)
                    @if ($theme === 'styled')
                        @php [$variant, $decor] = $styledCells[$shelf['slot'] % count($styledCells)]; @endphp
                        @include('profile.partials.bookcase-shelf', $shared + ['shelf' => $shelf, 'decor' => $decor, 'variant' => 'bookcase__cell--' . $variant])
                    @else
                        @include('profile.partials.bookcase-shelf', $shared + ['shelf' => $shelf])
                    @endif
                @endforeach
            </div>
        @endif

        @if ($theme === 'library')
            <div class="bookcase__base" aria-hidden="true"></div>
        @elseif ($theme === 'styled')
            <div class="styled__base" aria-hidden="true"><span class="nook__count">{{ $treeBookCount }} {{ Str::plural('book', $treeBookCount) }}</span></div>
        @elseif ($theme === 'mantel')
            {{-- The fireplace --}}
            <div class="bookcase__fireplace" aria-hidden="true">
                <div class="bookcase__mantel">
                    <div class="bookcase__garland"></div>
                    <div class="bookcase__stockings">
                        @foreach ([['#A3324A', $initial], ['#2F6B4F', '★'], ['#C99A45', '♥']] as [$color, $mark])
                            <span class="bookcase__stocking" style="--sock: {{ $color }}; --delay: -{{ $loop->index * 1.1 }}s;"><b>{{ $mark }}</b></span>
                        @endforeach
                    </div>
                </div>
                <div class="bookcase__hearth">
                    <div class="bookcase__firebox">
                        <span class="bookcase__flame bookcase__flame--1"></span>
                        <span class="bookcase__flame bookcase__flame--2"></span>
                        <span class="bookcase__flame bookcase__flame--3"></span>
                        <span class="bookcase__logs"></span>
                        <span class="bookcase__embers"></span>
                    </div>
                </div>
                <span class="bookcase__count">{{ $treeBookCount }} {{ Str::plural('book', $treeBookCount) }}</span>
            </div>
            <div class="bookcase__snow" aria-hidden="true"></div>
        @endif

        @if ($treeBookCount === 0)
            <div class="book-tree__empty">
                <p class="font-serif text-2xl font-semibold text-ink dark:text-[#f4e9e2]">{{ $isOwnProfile ? 'Fill your first shelf' : 'These shelves are still waiting for books' }}</p>
                <p class="mt-1 text-sm text-walnut dark:text-[#d4c2b9]">{{ $isOwnProfile ? 'Search for a book and add it — it will appear on a shelf right away.' : 'Reviewed books will appear here.' }}</p>
                @if ($isOwnProfile)
                    <a href="{{ route('books.index') }}" class="btn-primary mt-4">Find a book</a>
                @endif
            </div>
        @endif
    </div>

    @if ($isOwnProfile)
        <template data-empty-slot-template>
            <a href="{{ route('books.index') }}" class="tree-slot-empty" title="Add a book to this shelf"><span aria-hidden="true">+</span><span class="sr-only">Add a book</span></a>
        </template>
    @endif
</div>
