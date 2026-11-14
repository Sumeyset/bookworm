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
@endphp

@push('scripts')
    @vite('resources/js/tree-shelf.js')
@endpush

<div class="space-y-5">
    {{-- Growth banner --}}
    <div class="flex flex-col gap-4 rounded-3xl border border-warmborder bg-paper/80 p-5 shadow-bookplate backdrop-blur dark:border-[#4B3035] dark:bg-raised/80 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex items-center gap-4">
            <span class="flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl bg-gradient-to-br from-[#7FA35B] to-[#4E6B39] text-white shadow-md" aria-hidden="true">
                <svg class="h-7 w-7" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 21v-8"/><path d="M12 13c0-4 3-7 7-7 0 4-3 7-7 7Z"/><path d="M12 15c0-3.3-2.7-6-6-6 0 3.3 2.7 6 6 6Z"/></svg>
            </span>
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-tan dark:text-[#c7a491]">{{ $stageName }} · {{ $branchCount }} {{ Str::plural('branch', $branchCount) }}</p>
                <h3 class="mt-0.5 font-serif text-2xl font-semibold text-ink dark:text-[#f4e9e2]">{{ $isOwnProfile ? 'Your living library' : $profile->name . '’s living library' }}</h3>
                <p class="mt-0.5 text-sm text-walnut dark:text-[#d4c2b9]">{{ $stageText }}</p>
            </div>
        </div>
        @if ($isOwnProfile)
            <div class="flex flex-wrap items-center gap-2 text-xs text-tan dark:text-[#c7a491]">
                <span class="tree-legend tree-legend--want_to_read">Want to read</span>
                <span class="tree-legend tree-legend--reading">Reading</span>
                <span class="tree-legend tree-legend--read">Read</span>
            </div>
        @endif
    </div>

    @if ($isOwnProfile)
        {{-- Share & grow --}}
        <div class="flex flex-wrap items-center gap-2 rounded-3xl border border-dashed border-warmborder px-4 py-3 dark:border-[#4B3035]">
            <span class="mr-1 text-xs font-semibold uppercase tracking-[0.18em] text-tan">Share</span>
            <form method="POST" action="{{ route('share.toggle') }}">
                @csrf
                <button type="submit" class="bw-chip {{ $profile->is_public ? '!border-[#6d9a62] !text-[#4f7a46]' : '' }}" aria-pressed="{{ $profile->is_public ? 'true' : 'false' }}">
                    {{ $profile->is_public ? '🌍 Public link on' : '🔒 Turn on public link' }}
                </button>
            </form>
            @if ($profile->is_public)
                <button type="button" class="bw-chip" data-copy-link="{{ route('share.tree', $profile->username) }}">🔗 Copy link</button>
            @endif
            <button type="button" class="bw-chip" data-share-tree-image data-name="{{ $profile->name }}" data-count="{{ $treeBookCount }}">🖼️ Save tree as image</button>
            <a href="{{ route('year.show', $profile->username) }}" class="bw-chip">✨ {{ now()->year }} in books</a>
            <a href="{{ route('import.show') }}" class="bw-chip ml-auto">⬆️ Import from Goodreads</a>
        </div>
    @endif

    @if ($isOwnProfile && $treeBookCount > 0)
        <p class="flex items-center gap-2 px-1 text-sm text-tan dark:text-[#c7a491]">
            <svg class="h-4 w-4 shrink-0 text-gold" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M10 2a6 6 0 0 0-3.5 10.9V15a1 1 0 0 0 1 1h5a1 1 0 0 0 1-1v-2.1A6 6 0 0 0 10 2Zm-2 16h4a1 1 0 0 1 0 2H8a1 1 0 0 1 0-2Z"/></svg>
            Tap a book to open it. Drag books between branches to rearrange (long-press on phones).
        </p>
    @endif

    @php
        $scene = \App\Support\TreeBackdrop::layout($branchCount);
        $treeImage = asset(\App\Support\TreeBackdrop::IMAGE);
        $sceneW = $scene['width'];
        $sceneH = $scene['height'];
        $cropW = \App\Support\TreeBackdrop::CROP_TO - \App\Support\TreeBackdrop::CROP_FROM;
    @endphp
    <div class="book-tree" role="region" aria-label="Tree-shaped bookshelf"
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
</div>

@include('profile.partials.book-popup', ['isOwnProfile' => $isOwnProfile, 'shelfOptions' => $shelfOptions])

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
