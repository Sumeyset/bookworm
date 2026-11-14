@php
    $book = $item->book;
    $payload = $item->treePayload($isOwnProfile ?? false);
    $statusKey = $payload['status'];
    $progress = ($isOwnProfile ?? false) ? $payload['progress'] : null;
    $label = $book->title . ($book->author_name ? ' by ' . $book->author_name : '') . ', ' . ($payload['status_label'] === 'Currently reading' ? 'Reading' : $payload['status_label']);
@endphp

<button type="button"
        class="tree-book tree-book--{{ $statusKey }}"
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
    <span class="tree-book__ribbon" aria-hidden="true"></span>
    <span class="tree-book__progress" aria-hidden="true" @if ($progress === null || $statusKey !== 'reading') hidden @endif>
        <span style="width: {{ $progress ?? 0 }}%"></span>
    </span>
</button>
