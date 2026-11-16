{{-- A ribbed pumpkin with a leaf (72×60). $tone: orange|gold --}}
@php
    $u = 'dp' . Str::random(5);
    [$light, $mid, $dark] = ($tone ?? 'orange') === 'gold' ? ['#FFD983', '#EDB14A', '#B07A1E'] : ['#FFB45C', '#EB7B2C', '#A9480F'];
@endphp
<svg {!! $attrs ?? '' !!} viewBox="0 0 72 60" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs><radialGradient id="{{ $u }}" cx=".4" cy=".35" r=".85"><stop offset="0" stop-color="{{ $light }}"/><stop offset=".6" stop-color="{{ $mid }}"/><stop offset="1" stop-color="{{ $dark }}"/></radialGradient></defs>
    <ellipse cx="36" cy="58" rx="29" ry="2.4" fill="#000" opacity=".2"/>
    <g stroke="#3E1A1C" stroke-width="1.4">
        <ellipse cx="18" cy="36" rx="14" ry="20" fill="url(#{{ $u }})"/>
        <ellipse cx="54" cy="36" rx="14" ry="20" fill="url(#{{ $u }})"/>
        <ellipse cx="27" cy="36" rx="13" ry="21" fill="url(#{{ $u }})"/>
        <ellipse cx="45" cy="36" rx="13" ry="21" fill="url(#{{ $u }})"/>
        <ellipse cx="36" cy="36" rx="12" ry="22" fill="url(#{{ $u }})"/>
    </g>
    <path d="M36 15c-1-6 1-10.4 6-11.4" fill="none" stroke="#3E1A1C" stroke-width="6" stroke-linecap="round"/>
    <path d="M36 15c-1-6 1-10.4 6-11.4" fill="none" stroke="#6E7F36" stroke-width="3.6" stroke-linecap="round"/>
    <path d="M42 8c6-4.6 13.6-1.4 13.4 5-5.2 1.2-10.4-.2-13.4-5z" fill="#7E9A44" stroke="#3E1A1C" stroke-width="1.2" stroke-linejoin="round"/>
    <path d="M30 26c-1.4 4-1.6 8 0 12" stroke="#fff" stroke-width="2.4" opacity=".3" stroke-linecap="round"/>
</svg>
