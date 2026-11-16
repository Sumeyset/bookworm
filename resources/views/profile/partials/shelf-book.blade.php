@php
    $book = $item->book;
    $payload = $item->treePayload($isOwnProfile ?? false);
    // Sticky notes: friends can leave one on this book; the owner sees them all.
    $payload['notes'] = $item->stickyNotesFor(auth()->user());
    $payload['note_url'] = auth()->check() && auth()->id() !== $item->user_id ? route('shelf-notes.store', $item) : null;
    $noteCount = auth()->id() === $item->user_id ? count($payload['notes']) : 0;
    $statusKey = $payload['status'];
    $progress = ($isOwnProfile ?? false) ? $payload['progress'] : null;
    $label = $book->title . ($book->author_name ? ' by ' . $book->author_name : '') . ', ' . ($payload['status_label'] === 'Currently reading' ? 'Reading' : $payload['status_label']);
    // Spine colour and size for shelf styles that show books side-on.
    $spines = ['#7A2133', '#2F5D48', '#1F3A5F', '#C99A45', '#8A5A36', '#4A1220', '#B8402B', '#3F6FB5', '#5B6B2F', '#2A1A1D', '#E3A63B', '#6C3B85', '#D9C6B6', '#A3324A', '#356B6B'];
    $spineColor = $spines[crc32((string) $book->id) % count($spines)];
    $spineLight = in_array($spineColor, ['#C99A45', '#E3A63B', '#D9C6B6'], true);
    $spineSize = 86 + crc32('h' . $book->id) % 15; // height in %, so a row of spines isn't perfectly even
    $spineThick = 26 + crc32('w' . $book->id) % 12;
    $variant = crc32('v' . $book->id) % 4; // cloth, leather, paperback, linen
    // Books with a cover: the spine takes the cover's colours and a slice of its picture.
    $coverSpine = (bool) $book->cover_url;
    if ($coverSpine && ($fromCover = \App\Support\SpineColor::for($book))) {
        [$spineColor, $spineInk] = $fromCover;
        $spineLight = $spineInk !== '#FFF4DF';
    }
@endphp

<button type="button"
        class="tree-book tree-book--{{ $statusKey }} tree-book--v{{ $variant }} {{ $coverSpine ? 'tree-book--cover-spine' : '' }}"
        style="--spine: {{ $spineColor }}; --spine-size: {{ $spineSize }}%;@if ($coverSpine) --spine-img: url('{{ $book->cover_src }}');@endif"
        data-book-id="{{ $book->id }}"
        data-book="{{ json_encode($payload) }}"
        aria-label="{{ $label }}"
        aria-haspopup="dialog">
    <span class="tree-book__cover">
        @if ($book->cover_url)
            <img src="{{ $book->cover_src }}" alt="Cover of {{ $book->title }}" loading="lazy" decoding="async" draggable="false" width="120" height="180"
                 onerror="this.remove()">
        @endif
        <span class="tree-book__fallback">
            <span class="tree-book__fallback-title">{{ $book->title }}</span>
            <span class="tree-book__fallback-author">{{ $book->author_name ?? '' }}</span>
        </span>
    </span>
    <span class="tree-book__spine {{ $spineLight ? 'tree-book__spine--light' : '' }}" aria-hidden="true">
        <span class="tree-book__spine-label"><span class="tree-book__spine-title">{{ Str::limit($book->title, 34) }}</span></span>
        @if ($book->author_name)<span class="tree-book__spine-author">{{ Str::limit(Str::afterLast($book->author_name, ' '), 12) }}</span>@endif
    </span>
    <span class="tree-book__ribbon" aria-hidden="true"></span>
    @if ($noteCount)<span class="tree-book__sticky" title="{{ $noteCount }} sticky {{ Str::plural('note', $noteCount) }}">{{ $noteCount }}</span>@endif
    <span class="tree-book__progress" aria-hidden="true" @if ($progress === null || $statusKey !== 'reading') hidden @endif>
        <span style="width: {{ $progress ?? 0 }}%"></span>
    </span>
</button>
