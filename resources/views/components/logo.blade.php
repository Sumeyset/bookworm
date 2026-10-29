@props(['variant' => 'full', 'tone' => 'dark'])

@php
    $worm = $tone === 'light' ? '#F4E9E2' : '#6D1A2B';
    $wormHighlight = $tone === 'light' ? '#D1A24E' : '#8B2A3D';
    $wordmark = $tone === 'light' ? 'text-[#FFF8EF]' : 'text-burgundy';
    $tagline = $tone === 'light' ? 'text-[#F4E9E2]/80' : 'text-tan';
@endphp

<span {{ $attributes->class(['inline-flex items-center gap-2.5']) }}>
    <svg class="h-full max-h-14 w-auto shrink-0" viewBox="0 0 72 72" fill="none" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path d="M7 34.5c9-4.5 17.4-3.9 29 1.8v24.2c-10-5.3-19.7-5.9-29-1.7V34.5Z" fill="#5A3A2E"/>
        <path d="M65 34.5c-9-4.5-17.4-3.9-29 1.8v24.2c10-5.3 19.7-5.9 29-1.7V34.5Z" fill="#3B2418"/>
        <path d="M11 37c7.5-3.2 15.3-2.2 24 2v17c-8.6-3.9-16.5-4.7-24-2V37Z" fill="#F7F1EA"/>
        <path d="M61 37c-7.5-3.2-15.3-2.2-24 2v17c8.6-3.9 16.5-4.7 24-2V37Z" fill="#EFE3D3"/>
        <path d="M36 39v20" stroke="#B8893B" stroke-width="1.5"/>
        <path d="M35 39c-6-4-7-8-4.5-12.4 2.4-4.4 8.7-5.6 9.6-11.4" stroke="{{ $worm }}" stroke-width="6.5" stroke-linecap="round"/>
        <circle cx="41" cy="13" r="7" fill="{{ $wormHighlight }}"/>
        <circle cx="38.7" cy="11.5" r="0.8" fill="#2A1A1D"/>
        <circle cx="43.2" cy="11.5" r="0.8" fill="#2A1A1D"/>
        <path d="M39.5 15c.8.8 2.5.8 3.3 0" stroke="#2A1A1D" stroke-width="0.9" stroke-linecap="round"/>
        <path d="M13.5 40v10m45-10v10" stroke="#B8893B" stroke-opacity=".55" stroke-width="1"/>
    </svg>
    @if ($variant === 'full')
        <span class="flex min-w-0 flex-col">
            <span class="font-serif text-2xl font-semibold leading-none tracking-tight {{ $wordmark }}">Bookworm</span>
            <span class="mt-1 font-hand text-xl leading-none {{ $tagline }}">A home for readers</span>
        </span>
    @endif
</span>
