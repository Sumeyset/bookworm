{{--
    The living book tree.
    - The shelf is the tree picture in public/images/tree/tree-shelf.webp.
      It shows 7 planks and grows two planks taller at a time
      (see App\Support\TreeBackdrop and App\Support\BookTree).
    - Owners can drag books between branches (mouse, or long-press on touch)
      and tap a book to open it like a real book: details, rating and note on
      the left page; status and bookmark on the right page.
--}}
@php
    [$stageName, $stageText] = \App\Support\BookTree::stage($treeBookCount);
    $branchCount = $treeShelves->count();
    $capacity = \App\Models\UserBookShelf::MAX_BOOKS_PER_SHELF;
    $shelfOptions = $treeShelves->map(fn ($s) => ['slot' => $s['slot'], 'title' => $s['title']])->values();
    $shelfStyle = \App\Support\ShelfStyle::for($profile);
    $decorations = $shelfStyle['scene'] ? [] : \App\Support\Acorns::enabled($profile);
    $decorClasses = collect($decorations)->map(fn ($item) => 'decor-' . $item)->implode(' ');

    // Growth: how far to the next stage of the tree.
    $stages = [[1, 'Sapling'], [9, 'Young oak'], [18, 'Grand oak'], [30, 'Ancient arbor']];
    $next = collect($stages)->first(fn ($s) => $treeBookCount < $s[0]);
    $previous = collect($stages)->last(fn ($s) => $treeBookCount >= $s[0]);
    $stageProgress = $next ? (int) round(($treeBookCount - ($previous[0] ?? 0)) / max(1, $next[0] - ($previous[0] ?? 0)) * 100) : 100;
    $shelfCounts = collect($treeShelves)->flatMap(fn ($s) => $s['items'])->countBy('status');
    $styleThumb = match ($shelfStyle['scene']) {
        'christmas' => 'images/shelves/christmas-tree-shelf.webp',
        'wall' => 'images/shelves/wall-shelf.webp',
        'window' => 'images/shelves/window-nook.webp',
        default => \App\Support\TreeBackdrop::IMAGE,
    };
@endphp

@push('scripts')
    @vite(['resources/css/shelves.css', 'resources/js/tree-shelf.js'])
@endpush

<div class="space-y-5">
    {{-- The shelf hub: how the library is growing, and everything you can do with it --}}
    <section class="shelf-hub" aria-label="Your library">
        <div class="shelf-hub__top">
            <div class="shelf-hub__badge" aria-hidden="true">
                <svg viewBox="0 0 48 48"><circle cx="24" cy="24" r="22" fill="none" stroke="currentColor" stroke-opacity=".18" stroke-width="4"/><circle cx="24" cy="24" r="22" fill="none" stroke="currentColor" stroke-width="4" stroke-linecap="round" stroke-dasharray="{{ round(138.2 * $stageProgress / 100, 1) }} 138.2" transform="rotate(-90 24 24)"/></svg>
                <span>🌱</span>
            </div>
            <div class="min-w-0 flex-1">
                <p class="shelf-hub__eyebrow">{{ $stageName }} · {{ $treeBookCount }} {{ Str::plural('book', $treeBookCount) }}</p>
                <h3 class="shelf-hub__title">{{ $isOwnProfile ? 'Your living library' : $profile->name . '’s living library' }}</h3>
                <p class="shelf-hub__text">
                    @if ($next)
                        {{ $next[0] - $treeBookCount }} more {{ Str::plural('book', $next[0] - $treeBookCount) }} until it grows into a <b>{{ $next[1] }}</b>.
                    @else
                        {{ $stageText }}
                    @endif
                </p>
            </div>
            <dl class="shelf-hub__stats">
                @foreach ([['reading', 'Reading', '#E0A74A'], ['read', 'Read', '#6D9A62'], ['want_to_read', 'Want to read', '#6F9EC4']] as [$key, $label, $color])
                    <div style="--dot: {{ $color }}">
                        <dd>{{ $shelfCounts[$key] ?? 0 }}</dd>
                        <dt>{{ $label }}</dt>
                    </div>
                @endforeach
            </dl>
        </div>

        @if ($isOwnProfile)
            <div class="shelf-hub__actions">
                <button type="button" class="shelf-hub__action" data-open-shelf-styles>
                    <span class="shelf-hub__thumb" style="background-image: url('{{ \App\Support\StaticAsset::url($styleThumb) }}')" aria-hidden="true"></span>
                    <span class="min-w-0"><span class="shelf-hub__label">Shelf style</span><span class="shelf-hub__sub">{{ $shelfStyle['name'] }}</span></span>
                </button>
                <a href="{{ route('shop.index') }}" class="shelf-hub__action">
                    <span class="shelf-hub__icon shelf-hub__icon--acorn" aria-hidden="true">🌰</span>
                    <span class="min-w-0"><span class="shelf-hub__label">Tree shop</span><span class="shelf-hub__sub">{{ number_format($profile->acorns) }} acorns to spend</span></span>
                </a>
                @if (\App\Support\Acorns::enabled($profile))
                    <button type="button" class="shelf-hub__action" data-arrange-decor>
                        <span class="shelf-hub__icon shelf-hub__icon--year" aria-hidden="true">✋</span>
                        <span class="min-w-0"><span class="shelf-hub__label">Arrange decorations</span><span class="shelf-hub__sub">Drag them where you like</span></span>
                    </button>
                @endif
                <div class="shelf-hub__action shelf-hub__action--share">
                    <span class="shelf-hub__icon {{ $profile->is_public ? 'shelf-hub__icon--on' : '' }}" aria-hidden="true">{{ $profile->is_public ? '🌍' : '🔒' }}</span>
                    <span class="min-w-0 flex-1">
                        <span class="shelf-hub__label">Public link</span>
                        <span class="shelf-hub__sub">
                            @if ($profile->is_public)
                                <button type="button" class="shelf-hub__link" data-copy-link="{{ route('share.tree', $profile->username) }}">Copy link</button>
                                @unless ($shelfStyle['scene'])
                                    · <button type="button" class="shelf-hub__link" data-share-tree-image data-name="{{ $profile->name }}" data-count="{{ $treeBookCount }}">Save image</button>
                                @endunless
                            @else
                                Only you can see your shelf
                            @endif
                        </span>
                    </span>
                    <form method="POST" action="{{ route('share.toggle') }}">
                        @csrf
                        <button type="submit" class="shop-toggle" role="switch" aria-checked="{{ $profile->is_public ? 'true' : 'false' }}" aria-label="Public link">
                            <span class="shop-toggle__track"><span class="shop-toggle__dot"></span></span>
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </section>

    @if ($isOwnProfile)
        {{-- Shown while arranging decorations --}}
        <div class="decor-arrange-bar" data-arrange-bar data-style="{{ $shelfStyle['key'] }}" data-save-url="{{ route('decor.positions') }}" hidden>
            <span class="text-lg" aria-hidden="true">✋</span>
            <p class="min-w-0 flex-1"><b>Arrange your decorations.</b> Drag any of them to a new spot on this shelf.</p>
            <button type="button" class="btn-ghost min-h-0 py-2" data-arrange-reset>Reset</button>
            <button type="button" class="btn-primary min-h-0 py-2" data-arrange-done>Done</button>
        </div>
    @endif

    @if ($isOwnProfile && $treeBookCount > 0)
        <p class="flex items-center gap-2 px-1 text-sm text-tan dark:text-[#c7a491]">
            <svg class="h-4 w-4 shrink-0 text-gold" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 2a6 6 0 0 0-3.5 10.9V15a1 1 0 0 0 1 1h5a1 1 0 0 0 1-1v-2.1A6 6 0 0 0 10 2Zm-2 16h4a1 1 0 0 1 0 2H8a1 1 0 0 1 0-2Z"/></svg>
            Tap a book to open it. Drag books between shelves to rearrange (long-press on phones).
        </p>
    @endif

    @if ($shelfStyle['scene'])
        @include('profile.partials.photo-shelf', ['sceneKey' => $shelfStyle['scene'], 'decorations' => \App\Support\Acorns::enabled($profile)])
    @else
    @php
        $scene = \App\Support\TreeBackdrop::layout($branchCount);
        $treeImage = \App\Support\StaticAsset::url(\App\Support\TreeBackdrop::IMAGE);
        $sceneW = $scene['width'];
        $sceneH = $scene['height'];
        $cropW = \App\Support\TreeBackdrop::CROP_TO - \App\Support\TreeBackdrop::CROP_FROM;
    @endphp
    <div class="book-tree {{ $decorClasses ?? '' }}" role="region" aria-label="Tree-shaped bookshelf"
         data-book-tree
         data-editable="{{ $isOwnProfile ? 'true' : 'false' }}"
         data-capacity="{{ $capacity }}"
         data-shelves='@json($shelfOptions)'
         style="--scene-w: {{ $sceneW }}; --crop-w: {{ $cropW }}; --crop-x: {{ \App\Support\TreeBackdrop::CROP_FROM }}; --scene-ar: {{ $sceneW }} / {{ $sceneH }}; --crop-ar: {{ $cropW }} / {{ $sceneH }};">
        <div class="book-tree__frame">
            <div class="book-tree__scene">
                {{-- The picture, stacked so the trunk can grow taller (one download, reused) --}}
                @foreach ($scene['segments'] as $i => $segment)
                    @php $segH = $segment['to'] - $segment['from']; @endphp
                    <div class="book-tree__segment {{ $segment['fade'] ? 'book-tree__segment--fade' : '' }}" aria-hidden="true"
                         style="top: {{ round($segment['at'] / $sceneH * 100, 4) }}%; height: {{ round($segH / $sceneH * 100, 4) }}%; --fade: {{ round(\App\Support\TreeBackdrop::FADE / $segH * 100, 3) }}%;">
                        <img src="{{ $treeImage }}" alt="" width="{{ $sceneW }}" height="{{ \App\Support\TreeBackdrop::HEIGHT }}" decoding="async" @if ($i === 0) fetchpriority="high" @endif
                             style="top: {{ round(-$segment['from'] / $segH * 100, 4) }}%;">
                    </div>
                @endforeach

                @include('profile.partials.tree-decor', ['decorations' => $decorations ?? [], 'branchCount' => $branchCount, 'positions' => ($profile->decor_positions ?? [])['tree'] ?? []])

                @php
                    // Only the lowest empty shelf shows "+" slots; the others keep the picture clean until you drag a book.
                    $firstEmptySlot = $treeShelves->first(fn ($s) => $s['items']->isEmpty())['slot'] ?? null;
                @endphp
                @foreach ($treeShelves->reverse() as $shelf)
                    @php
                        $slot = $shelf['slot'];
                        $quiet = $shelf['items']->isEmpty() && $slot !== $firstEmptySlot;
                        $pos = $scene['shelves'][$slot];
                        $emptyCount = max(0, $capacity - $shelf['items']->count());
                    @endphp
                    <div class="tree-shelf {{ $shelf['sprouting'] ? 'tree-shelf--sprouting' : '' }} {{ $quiet ? 'tree-shelf--quiet' : '' }}"
                         data-slot="{{ $slot }}" role="group" aria-label="{{ $shelf['title'] }}"
                         style="left: {{ $pos['left'] }}%; bottom: {{ $pos['bottom'] }}%; width: {{ $pos['width'] }}%;">
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
                    <p class="font-serif text-2xl font-semibold text-ink dark:text-[#f4e9e2]">{{ $isOwnProfile ? 'Plant your first book' : 'This tree is still waiting for books' }}</p>
                    <p class="mt-1 text-sm text-walnut dark:text-[#d4c2b9]">{{ $isOwnProfile ? 'Search for a book and add it to your shelf — it will appear on a plank right away.' : 'Reviewed books will appear here.' }}</p>
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
    @endif
</div>

@include('profile.partials.book-popup', ['isOwnProfile' => $isOwnProfile, 'shelfOptions' => $shelfOptions])

@if ($isOwnProfile)
    @include('profile.partials.shelf-style-picker', ['current' => $shelfStyle['key']])
@endif

@if ($isOwnProfile)
    <dialog id="rename-shelf-dialog" class="bw-dialog" aria-labelledby="rename-shelf-title">
        <form method="POST" action="" data-rename-form class="space-y-4 p-6">
            @csrf
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-tan dark:text-[#c7a491]">Branch nameplate</p>
                <h4 id="rename-shelf-title" class="mt-1 font-serif text-2xl font-semibold text-ink dark:text-[#f4e9e2]">Rename this shelf</h4>
            </div>
            <label for="shelf-title-input" class="sr-only">Shelf name</label>
            <input id="shelf-title-input" name="title" required maxlength="50" placeholder="e.g. Cozy mysteries" class="bw-input w-full font-serif">
            <div class="flex justify-end gap-2">
                <button type="button" class="btn-ghost" data-close-dialog>Cancel</button>
                <button type="submit" class="btn-primary">Save name</button>
            </div>
        </form>
    </dialog>
@endif
