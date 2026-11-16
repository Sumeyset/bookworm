{{--
    A photo bookshelf (App\Support\ShelfScene): the reader's books placed on
    the picture's real shelves. Uses the same .tree-shelf / .tree-book pieces
    as the tree, so dragging, the book popup and renaming all work the same.
    When the books outgrow the picture, the picture grows (ShelfScene 'grow').
    With the Christmas set from the Tree shop, snow falls on every style.
--}}
@php
    $scene = \App\Support\ShelfScene::layout($sceneKey, \App\Support\ShelfScene::bandsFor($sceneKey, $treeShelves->count()));
    $sceneShelfCount = count($scene['shelves']);
    $inScene = $treeShelves->filter(fn ($s) => $s['slot'] < $sceneShelfCount);
    $decorations = $decorations ?? [];
    $snow = in_array('snow', $decorations, true);
    // Shop themes change the window nook's view: evening brings the night, snow winter, autumn autumn.
    $fixedTime = in_array('evening', $decorations, true) ? 'night' : null;
    $fixedSeason = $snow ? 'winter' : (in_array('autumn', $decorations, true) ? 'autumn' : null);
    $firstEmptySlot = $treeShelves->first(fn ($s) => $s['items']->isEmpty())['slot'] ?? null;
    $season = \App\Support\ShelfStyle::season(now());
    $hour = (int) now()->format('G');
    $part = match (true) { $hour >= 5 && $hour < 8 => 'dawn', $hour >= 8 && $hour < 17 => 'day', $hour >= 17 && $hour < 20 => 'dusk', default => 'night' };
    $part = $fixedTime ?? $part;
    $season = $fixedSeason ?? $season;
@endphp
<div class="book-tree photo-shelf photo-shelf--{{ $sceneKey }} photo-shelf--{{ $scene['view'] }} {{ collect($decorations)->map(fn ($item) => 'decor-' . $item)->implode(' ') }}" role="region" aria-label="{{ \App\Support\ShelfStyle::STYLES[$sceneKey][0] }}"
     data-book-tree
     data-editable="{{ $isOwnProfile ? 'true' : 'false' }}"
     data-capacity="{{ $capacity }}"
     data-shelves='@json($shelfOptions)'
     @if ($scene['glass']) data-window-scene data-time="{{ $part }}" data-season="{{ $season }}" data-view-base="{{ asset('images/shelves/window') }}" data-view-version="{{ \App\Support\StaticAsset::folderVersion('images/shelves/window') }}" @if ($fixedTime) data-fixed-time="{{ $fixedTime }}" @endif @if ($fixedSeason) data-fixed-season="{{ $fixedSeason }}" @endif @endif
     style="--scene-w: {{ $scene['width'] }}; --scene-ar: {{ $scene['width'] }} / {{ $scene['height'] }};@if ($scene['glass']) --glass-l: {{ $scene['glass']['left'] }}%; --glass-t: {{ $scene['glass']['top'] }}%; --glass-r: {{ $scene['glass']['left'] + $scene['glass']['width'] }}%; --glass-b: {{ $scene['glass']['top'] + $scene['glass']['height'] }}%;@endif --min-w: {{ $scene['min_width'] }}px; --decor-scale: {{ $scene['decor_scale'] ?? 1 }}; --spine-px: {{ $scene['spine'] ?? 20 }}; --book-px: {{ $scene['book'] ?? 74 }};">
    <div class="book-tree__frame photo-shelf__frame" data-scroll-frame>
        <div class="book-tree__scene photo-shelf__scene">
            @if ($scene['glass'])
                {{-- The view outside, behind the glass --}}
                <div class="photo-shelf__view" style="left: {{ $scene['glass']['left'] }}%; top: {{ $scene['glass']['top'] }}%; width: {{ $scene['glass']['width'] }}%; height: {{ $scene['glass']['height'] }}%;" aria-hidden="true">
                    <img src="{{ \App\Support\StaticAsset::url("images/shelves/window/{$season}-{$part}.webp") }}" alt="" data-window-view>
                    <img alt="" data-window-view-next class="is-waiting">
                </div>
            @endif
            @if ($scene['glass'] && $snow)
                {{-- snow falling outside the window --}}
                <div class="photo-shelf__view photo-shelf__outside-snow" style="left: {{ $scene['glass']['left'] }}%; top: {{ $scene['glass']['top'] }}%; width: {{ $scene['glass']['width'] }}%; height: {{ $scene['glass']['height'] }}%;" aria-hidden="true">
                    <span class="tree-fx__snow"></span>
                    <span class="tree-fx__snow tree-fx__snow--far"></span>
                </div>
            @endif
            @if ($scene['pieces'])
                {{-- the picture, grown: top, repeated bands of shelves, then the rest --}}
                <div class="photo-shelf__img photo-shelf__img--grown" aria-hidden="true">
                    @foreach ($scene['pieces'] as $piece)
                        <div class="photo-shelf__piece" style="top: {{ $piece['top'] }}%; height: calc({{ $piece['height'] }}% + 1px);">
                            <img src="{{ \App\Support\StaticAsset::url($piece['image']) }}" alt="" style="margin-top: -{{ $piece['shift'] }}%;" decoding="async" draggable="false">
                        </div>
                    @endforeach
                </div>
            @else
                <img class="photo-shelf__img" src="{{ \App\Support\StaticAsset::url($scene['image']) }}" alt="" width="{{ $scene['width'] }}" height="{{ $scene['height'] }}" decoding="async" fetchpriority="high" draggable="false">
            @endif
            @if ($scene['sash'])
                {{-- The bottom sashes: tap the window to slide them up --}}
                <img class="photo-shelf__sash" src="{{ \App\Support\StaticAsset::url($scene['sash']['image']) }}" alt="" draggable="false"
                     style="left: {{ $scene['sash']['left'] }}%; top: {{ $scene['sash']['top'] }}%; width: {{ $scene['sash']['width'] }}%; height: {{ $scene['sash']['height'] }}%; --open: -{{ $scene['sash']['open'] }}%;">
                <div class="photo-shelf__breeze" aria-hidden="true" style="left: {{ $scene['glass']['left'] }}%; top: {{ $scene['glass']['top'] }}%; width: {{ $scene['glass']['width'] }}%; height: {{ $scene['glass']['height'] }}%;">
                    @foreach (range(0, 9) as $i)
                        <span style="--x: {{ 8 + ($i * 37 % 84) }}%; --y: {{ 50 + ($i * 23 % 45) }}%; --d: {{ 4 + ($i % 4) * 1.3 }}s; --delay: -{{ $i * 0.7 }}s; --drift: {{ ($i % 2 ? 1 : -1) * (40 + $i * 9) }}px;"></span>
                    @endforeach
                </div>
                <button type="button" class="photo-shelf__window-toggle" data-window-toggle aria-pressed="false" aria-label="Open the window"
                        style="left: {{ $scene['glass']['left'] }}%; top: {{ $scene['glass']['top'] }}%; width: {{ $scene['glass']['width'] }}%; height: {{ $scene['glass']['height'] }}%;">
                    <span class="photo-shelf__window-hint">Tap to open the window</span>
                </button>
            @endif
            @if ($scene['glass'])
                <span class="photo-shelf__light" aria-hidden="true"></span>
            @endif

            @foreach ($inScene as $shelf)
                @php
                    $slot = $shelf['slot'];
                    $pos = $scene['shelves'][$slot];
                    $quiet = $shelf['items']->isEmpty() && $slot !== $firstEmptySlot;
                    $emptyCount = max(0, $pos['cap'] - $shelf['items']->count());
                @endphp
                <div class="tree-shelf photo-shelf__shelf photo-shelf__shelf--{{ $pos['align'] }} {{ $pos['cap'] === 1 ? 'photo-shelf__shelf--single' : '' }} {{ $pos['pile'] ? 'photo-shelf__shelf--pile' : '' }} {{ $pos['lean'] ? 'photo-shelf__shelf--leans photo-shelf__shelf--lean-' . ($pos['lean'] > 0 ? 'right' : 'left') : '' }} {{ $quiet ? 'tree-shelf--quiet' : '' }}" data-capacity="{{ $pos['cap'] }}"
                     data-slot="{{ $slot }}" role="group" aria-label="{{ $shelf['title'] }}"
                     style="left: {{ $pos['left'] }}%; bottom: {{ $pos['bottom'] }}%; width: {{ $pos['width'] }}%; --max-h: {{ $pos['h'] }};@if ($pos['lean']) --lean: {{ $pos['lean'] }}deg; --lean-room: {{ $pos['room'] }}; --lean-gap: {{ $pos['lean_gap'] }};@endif">
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

            @if ($decorations && $scene['decor'])
                @include('profile.partials.tree-decor', ['decorations' => $decorations, 'spots' => $scene['decor'], 'windowSnow' => (bool) $scene['glass'], 'positions' => ($profile->decor_positions ?? [])[$sceneKey] ?? []])
            @endif

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

    @if ($isOwnProfile)
        <template data-empty-slot-template>
            <a href="{{ route('books.index') }}" class="tree-slot-empty" title="Add a book to this shelf"><span aria-hidden="true">+</span><span class="sr-only">Add a book</span></a>
        </template>
    @endif
</div>
