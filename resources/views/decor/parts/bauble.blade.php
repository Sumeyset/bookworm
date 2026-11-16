{{-- Glass bauble on a string (40×64). $tone: red|gold|green|blue, $pattern: 0|1 --}}
@php
    $u = 'db' . Str::random(5);
    [$light, $mid, $dark] = ['red' => ['#FF9EA6', '#D3343F', '#7A1220'], 'gold' => ['#FFF0B0', '#E0AC3C', '#86591A'], 'green' => ['#9BE6BE', '#2F8A5E', '#114029'], 'blue' => ['#B4D3FF', '#4778C2', '#183766']][$tone ?? 'red'];
@endphp
<svg {!! $attrs ?? '' !!} viewBox="0 0 40 64" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs><radialGradient id="{{ $u }}" cx=".36" cy=".3" r=".78"><stop offset="0" stop-color="{{ $light }}"/><stop offset=".5" stop-color="{{ $mid }}"/><stop offset="1" stop-color="{{ $dark }}"/></radialGradient></defs>
    <path d="M20 0v13" stroke="#8C6A4A" stroke-width="1"/>
    <circle cx="20" cy="14.5" r="2.6" fill="none" stroke="#B88A2E" stroke-width="1.5"/>
    <rect x="14" y="16.5" width="12" height="7.5" rx="1.8" fill="#E9C46A" stroke="#3E1A1C" stroke-width="1.2"/>
    <path d="M16.5 17v7M20 17v7M23.5 17v7" stroke="#B88A2E" stroke-width=".8"/>
    <circle cx="20" cy="41.5" r="17" fill="url(#{{ $u }})" stroke="#3E1A1C" stroke-width="1.4"/>
    @if (($pattern ?? 0) % 2)
        <path d="M3.4 38c10 4.2 23.2 4.2 33.2 0M3.2 45c10 4.2 23.6 4.2 33.6 0" stroke="#FFE7A3" stroke-width="1.6" fill="none"/>
        <g fill="#FFE7A3"><circle cx="11" cy="41.6" r="1.1"/><circle cx="20" cy="42.4" r="1.1"/><circle cx="29" cy="41.6" r="1.1"/></g>
    @else
        <path d="M20 31.5l1.5 3.2 3.5.4-2.6 2.4.7 3.5-3.1-1.8-3.1 1.8.7-3.5-2.6-2.4 3.5-.4z" fill="#FFF6E0"/>
        <g fill="#FFF6E0" opacity=".85"><circle cx="11" cy="46" r="1.3"/><circle cx="20" cy="50" r="1.3"/><circle cx="29" cy="46" r="1.3"/></g>
    @endif
    <ellipse cx="13" cy="33.5" rx="4.6" ry="2.8" fill="#fff" opacity=".6" transform="rotate(-32 13 33.5)"/>
</svg>
