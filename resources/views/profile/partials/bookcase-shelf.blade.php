{{--
    One shelf inside a bookcase style. Expects $shelf, $firstEmptySlot,
    $capacity, $isOwnProfile, and optional $decor (list of decor kinds),
    $variant (extra class) and $initial.
--}}
@php
    $slot = $shelf['slot'];
    $quiet = $shelf['items']->isEmpty() && $slot !== $firstEmptySlot;
    $emptyCount = max(0, $capacity - $shelf['items']->count());
@endphp
<div class="tree-shelf bookcase__cell {{ $variant ?? '' }} {{ $shelf['sprouting'] ? 'tree-shelf--sprouting' : '' }} {{ $quiet ? 'tree-shelf--quiet' : '' }}"
     data-slot="{{ $slot }}" role="group" aria-label="{{ $shelf['title'] }}">
    <div class="bookcase__row">
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
        @if (! empty($decor))
            <div class="bookcase__decor">
                @foreach ($decor as $kind)
                    @include('profile.partials.shelf-decor', ['kind' => $kind, 'initial' => $initial ?? ''])
                @endforeach
            </div>
        @endif
    </div>
    <span class="bookcase__plank" aria-hidden="true"></span>
    @if ($isOwnProfile)
        <button type="button" class="tree-shelf__name" data-rename-slot="{{ $slot }}" data-title="{{ $shelf['title'] }}" aria-label="Rename shelf {{ $shelf['title'] }}">
            <span>{{ $shelf['title'] }}</span>
            <svg viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path d="M13.6 3.2a2 2 0 0 1 2.9 2.9l-8.8 8.8-3.6.9.9-3.6 8.6-9Z"/></svg>
        </button>
    @else
        <span class="tree-shelf__name"><span>{{ $shelf['title'] }}</span></span>
    @endif
</div>
