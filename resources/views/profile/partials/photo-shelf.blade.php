{{--
    A photo bookshelf (App\Support\ShelfScene): the reader's books placed on
    the picture's real shelves. Uses the same .tree-shelf / .tree-book pieces
    as the tree, so dragging, the book popup and renaming all work the same.
    Shelves beyond the picture's own appear underneath ("More shelves").
--}}
@php
    $scene = \App\Support\ShelfScene::layout($sceneKey);
    $sceneShelfCount = count($scene['shelves']);
    $inScene = $treeShelves->filter(fn ($s) => $s['slot'] < $sceneShelfCount);
    $extra = $treeShelves->filter(fn ($s) => $s['slot'] >= $sceneShelfCount);
    $firstEmptySlot = $treeShelves->first(fn ($s) => $s['items']->isEmpty())['slot'] ?? null;
    $season = \App\Support\ShelfStyle::season(now());
    $hour = (int) now()->format('G');
    $part = match (true) { $hour >= 5 && $hour < 8 => 'dawn', $hour >= 8 && $hour < 17 => 'day', $hour >= 17 && $hour < 20 => 'dusk', default => 'night' };
@endphp
<div class="book-tree photo-shelf photo-shelf--{{ $sceneKey }} photo-shelf--{{ $scene['view'] }}" role="region" aria-label="{{ \App\Support\ShelfStyle::STYLES[$sceneKey][0] }}"
     data-book-tree
     data-editable="{{ $isOwnProfile ? 'true' : 'false' }}"
     data-capacity="{{ $capacity }}"
     data-shelves='@json($shelfOptions)'
     @if ($scene['glass']) data-window-scene data-time="{{ $part }}" data-season="{{ $season }}" data-view-base="{{ asset('images/shelves/window') }}" @endif
     style="--scene-w: {{ $scene['width'] }}; --scene-ar: {{ $scene['width'] }} / {{ $scene['height'] }}; --min-w: {{ $scene['min_width'] }}px; --spine-px: {{ $scene['spine'] ?? 20 }}; --book-px: {{ $scene['book'] ?? 74 }};">
    <div class="book-tree__frame photo-shelf__frame" data-scroll-frame>
        <div class="book-tree__scene photo-shelf__scene">
            @if ($scene['glass'])
                {{-- The view outside, behind the glass --}}
                <div class="photo-shelf__view" style="left: {{ $scene['glass']['left'] }}%; top: {{ $scene['glass']['top'] }}%; width: {{ $scene['glass']['width'] }}%; height: {{ $scene['glass']['height'] }}%;" aria-hidden="true">
                    <img src="{{ asset("images/shelves/window/{$season}-{$part}.webp") }}" alt="" data-window-view decoding="async">
                    <img alt="" data-window-view-next decoding="async" hidden>
                </div>
            @endif
            <img class="photo-shelf__img" src="{{ asset($scene['image']) }}" alt="" width="{{ $scene['width'] }}" height="{{ $scene['height'] }}" decoding="async" fetchpriority="high" draggable="false">
            @if ($scene['glass'])
                <span class="photo-shelf__light" aria-hidden="true"></span>
            @endif

            @foreach ($inScene as $shelf)
                @php
                    $slot = $shelf['slot'];
                    $pos = $scene['shelves'][$slot];
                    $quiet = $shelf['items']->isEmpty() && $slot !== $firstEmptySlot;
                    $emptyCount = max(0, $capacity - $shelf['items']->count());
                @endphp
                <div class="tree-shelf photo-shelf__shelf photo-shelf__shelf--{{ $pos['align'] }} {{ $quiet ? 'tree-shelf--quiet' : '' }}"
                     data-slot="{{ $slot }}" role="group" aria-label="{{ $shelf['title'] }}"
                     style="left: {{ $pos['left'] }}%; bottom: {{ $pos['bottom'] }}%; width: {{ $pos['width'] }}%; --max-h: {{ $pos['h'] }};">
                    <div class="tree-shelf__books">
                        @foreach ($shelf['items'] as $item)
                            @include('profile.partials.shelf-book', ['item' => $item, 'isOwnProfile' => $isOwnProfile])
                        @endforeach
                        @if ($isOwnProfile)
                            @for ($i = 0; $i < $emptyCount; $i++)
                                <a href="{{ route('books.index') }}" class="tree-slot-empty" title="Add a book to this shelf"><span aria-hidden="true">+</span><span class="sr-only">Add a book</span></a>
                            @endfor
                        @endif
                    </div>
                    @if ($isOwnProfile)
                        <button type="button" class="tree-shelf__name" data-rename-slot="{{ $slot }}" data-title="{{ $shelf['title'] }}" aria-label="Rename shelf {{ $shelf['title'] }}">
                            <span>{{ $shelf['title'] }}</span>
                            <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M13.6 3.2a2 2 0 0 1 2.9 2.9l-8.8 8.8-3.6.9.9-3.6 8.6-9Z"/></svg>
                        </button>
                    @else
                        <span class="tree-shelf__name"><span>{{ $shelf['title'] }}</span></span>
                    @endif
                </div>
            @endforeach

            <span class="book-tree__sign" aria-hidden="true">{{ $treeBookCount }} {{ Str::plural('book', $treeBookCount) }}</span>
        </div>

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

    @if ($extra->isNotEmpty())
        {{-- More books than the picture has shelves for --}}
        <div class="photo-shelf__more">
            <p class="photo-shelf__more-title">More shelves</p>
            <div class="photo-shelf__more-list">
                @foreach ($extra as $shelf)
                    @php $emptyCount = max(0, $capacity - $shelf['items']->count()); @endphp
                    <div class="tree-shelf photo-shelf__extra" data-slot="{{ $shelf['slot'] }}" role="group" aria-label="{{ $shelf['title'] }}">
                        <div class="tree-shelf__books">
                            @foreach ($shelf['items'] as $item)
                                @include('profile.partials.shelf-book', ['item' => $item, 'isOwnProfile' => $isOwnProfile])
                            @endforeach
                            @if ($isOwnProfile)
                                @for ($i = 0; $i < $emptyCount; $i++)
                                    <a href="{{ route('books.index') }}" class="tree-slot-empty" title="Add a book to this shelf"><span aria-hidden="true">+</span><span class="sr-only">Add a book</span></a>
                                @endfor
                            @endif
                        </div>
                        @if ($isOwnProfile)
                            <button type="button" class="tree-shelf__name" data-rename-slot="{{ $shelf['slot'] }}" data-title="{{ $shelf['title'] }}" aria-label="Rename shelf {{ $shelf['title'] }}"><span>{{ $shelf['title'] }}</span></button>
                        @else
                            <span class="tree-shelf__name"><span>{{ $shelf['title'] }}</span></span>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    @if ($isOwnProfile)
        <template data-empty-slot-template>
            <a href="{{ route('books.index') }}" class="tree-slot-empty" title="Add a book to this shelf"><span aria-hidden="true">+</span><span class="sr-only">Add a book</span></a>
        </template>
    @endif
</div>
