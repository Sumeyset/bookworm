{{--
    A designed quote card (4:5). Also used for "thought" posts that were put on a card.
    Designs and fonts live in resources/css/posts.css; resources/js/quote-card.js builds the same markup.
--}}
@props(['text', 'design' => 'wine', 'font' => 'serif', 'title' => null, 'meta' => null, 'thought' => false])
@php
    $length = mb_strlen((string) $text);
    $size = match (true) {
        $length <= 80 => 's',
        $length <= 180 => 'm',
        $length <= 320 => 'l',
        $length <= 520 => 'xl',
        default => 'xxl',
    };
@endphp
<figure {{ $attributes->class(["qcard qcard--{$design} qcard--f-{$font} qcard--len-{$size}", 'qcard--thought' => $thought]) }}>
    <div class="qcard__inner">
        <span class="qcard__mark" aria-hidden="true">“</span>
        <blockquote class="qcard__text">{{ $text }}</blockquote>
        @if ($title || $meta)
            <figcaption class="qcard__credit">
                @if ($title)<span class="qcard__title">{{ $title }}</span>@endif
                @if ($meta)<span class="qcard__meta">{{ $meta }}</span>@endif
            </figcaption>
        @endif
    </div>
    <span class="qcard__brand" aria-hidden="true">Bookworm</span>
</figure>
