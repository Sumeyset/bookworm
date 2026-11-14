@props(['book'])
{{-- A book cover, served from our own cache (see CoverController), with a cloth-cover fallback. --}}
@if ($book?->cover_url)
    <img src="{{ $book->cover_src }}" alt="Cover of {{ $book->title }}" loading="lazy" decoding="async" width="160" height="240" data-cover-fallback="{{ Str::limit($book->title, 40) }}"
         {{ $attributes->class(['aspect-[2/3] rounded-lg object-cover shadow-[0_14px_24px_-12px_rgba(42,26,29,0.6)] ring-1 ring-black/5']) }}>
@else
    <span {{ $attributes->class(['flex aspect-[2/3] items-center justify-center rounded-lg bg-gradient-to-br from-burgundy via-[#8B2A3D] to-burgundy-deep p-1.5 text-center font-serif text-[10px] leading-tight text-[#FFF8EF] shadow-[0_14px_24px_-12px_rgba(42,26,29,0.6)]']) }}>{{ Str::limit($book?->title ?? 'Book', 40) }}</span>
@endif
