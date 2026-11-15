{{--
    "Book Christmas tree": the reader's books stacked into a cone, spines out,
    with lights and a big red bow. Each shelf is one layer of the tree (the
    first shelf is the wide bottom layer). Gaps are filled with plain books
    drawn in CSS, so the tree always looks full.
    Uses the same .tree-shelf / .tree-book pieces, so dragging, the book popup
    and renaming keep working.
--}}
@php
    $firstEmptySlot = $treeShelves->first(fn ($s) => $s['items']->isEmpty())['slot'] ?? null;
    $layers = $treeShelves->reverse()->values();
    $count = max(1, $layers->count() - 1);
@endphp
<div class="book-tree bookcase booktree" role="region" aria-label="Christmas tree made of books"
     data-book-tree
     data-editable="{{ $isOwnProfile ? 'true' : 'false' }}"
     data-capacity="{{ $capacity }}"
     data-shelves='@json($shelfOptions)'>
    <div class="bookcase__frame booktree__room">
        <div class="booktree__garland" aria-hidden="true"></div>

        <div class="booktree__tree">
            <svg class="booktree__bow" viewBox="0 0 120 80" aria-hidden="true">
                <path d="M60 34C44 6 10 0 8 20s30 26 52 14z" fill="#C8203A"/>
                <path d="M60 34c16-28 50-34 52-14s-30 26-52 14z" fill="#C8203A"/>
                <path d="M60 34C46 14 22 10 18 22c8-2 24 2 42 12zM60 34c14-20 38-24 42-12-8-2-24 2-42 12z" fill="#9E1529" opacity=".55"/>
                <path d="M56 36L40 78l10-4 6 6 4-40zM64 36l16 42-10-4-6 6-4-40z" fill="#B01A33"/>
                <ellipse cx="60" cy="34" rx="9" ry="8" fill="#E02A45"/>
            </svg>

            @foreach ($layers as $index => $shelf)
                @php
                    $slot = $shelf['slot'];
                    $quiet = $shelf['items']->isEmpty() && $slot !== $firstEmptySlot;
                    $emptyCount = max(0, $capacity - $shelf['items']->count());
                    $widthAt = fn ($i) => 22 + 70 * ($i / $count);
                    $width = round($widthAt($index), 2);
                    // Taper each layer so its top meets the layer above: a smooth cone, not steps.
                    $above = $index === 0 ? $width * 0.55 : $widthAt($index - 1);
                    $inset = round(($width - $above) / (2 * $width) * 100, 2);
                @endphp
                <div class="tree-shelf booktree__layer {{ $shelf['sprouting'] ? 'tree-shelf--sprouting' : '' }} {{ $quiet ? 'tree-shelf--quiet' : '' }}"
                     data-slot="{{ $slot }}" role="group" aria-label="{{ $shelf['title'] }}"
                     style="--w: {{ $width }}%; --inset: {{ $inset }}%; --tilt: {{ $index % 2 ? '-' : '' }}2.4deg;">
                    <div class="tree-shelf__books">
                        @foreach ($shelf['items'] as $item)
                            @include('profile.partials.shelf-book', ['item' => $item, 'isOwnProfile' => $isOwnProfile])
                        @endforeach
                        @if ($isOwnProfile)
                            @for ($i = 0; $i < $emptyCount; $i++)
                                <a href="{{ route('books.index') }}" class="tree-slot-empty" title="Add a book to this layer"><span aria-hidden="true">+</span><span class="sr-only">Add a book</span></a>
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
        </div>

        <div class="booktree__rug" aria-hidden="true">
            <svg class="booktree__gift" viewBox="0 0 60 50"><rect x="4" y="16" width="52" height="34" rx="2" fill="#2F6B4F"/><rect x="2" y="10" width="56" height="10" rx="2" fill="#3C8562"/><rect x="26" y="10" width="8" height="40" fill="#E9C27D"/><path d="M30 10c-6-9-16-6-12 0M30 10c6-9 16-6 12 0" fill="none" stroke="#E9C27D" stroke-width="3" stroke-linecap="round"/></svg>
            <svg class="booktree__gift booktree__gift--right" viewBox="0 0 60 50"><rect x="4" y="16" width="52" height="34" rx="2" fill="#F5EFE6"/><rect x="2" y="10" width="56" height="10" rx="2" fill="#FFFFFF"/><rect x="26" y="10" width="8" height="40" fill="#C8203A"/><path d="M30 10c-6-9-16-6-12 0M30 10c6-9 16-6 12 0" fill="none" stroke="#C8203A" stroke-width="3" stroke-linecap="round"/></svg>
        </div>
        <span class="bookcase__count booktree__count">{{ $treeBookCount }} {{ Str::plural('book', $treeBookCount) }}</span>

        @if ($treeBookCount === 0)
            <div class="book-tree__empty">
                <p class="font-serif text-2xl font-semibold text-ink dark:text-[#f4e9e2]">{{ $isOwnProfile ? 'Start your book tree' : 'This tree is still waiting for books' }}</p>
                <p class="mt-1 text-sm text-walnut dark:text-[#d4c2b9]">{{ $isOwnProfile ? 'Every book you add becomes part of the tree.' : 'Reviewed books will appear here.' }}</p>
                @if ($isOwnProfile)
                    <a href="{{ route('books.index') }}" class="btn-primary mt-4">Find a book</a>
                @endif
            </div>
        @endif
    </div>

    @if ($isOwnProfile)
        <template data-empty-slot-template>
            <a href="{{ route('books.index') }}" class="tree-slot-empty" title="Add a book to this layer"><span aria-hidden="true">+</span><span class="sr-only">Add a book</span></a>
        </template>
    @endif
</div>
