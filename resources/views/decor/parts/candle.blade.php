{{-- A lit candle in a brass holder (32×60) --}}
@php($u = 'dcd' . Str::random(5))
<svg {!! $attrs ?? '' !!} viewBox="0 0 32 60" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs><radialGradient id="{{ $u }}"><stop offset="0" stop-color="#FFE3A0" stop-opacity=".9"/><stop offset="1" stop-color="#FFE3A0" stop-opacity="0"/></radialGradient></defs>
    <circle cx="16" cy="12" r="14" fill="url(#{{ $u }})" class="decor-candle__glow"/>
    <path class="decor-candle__flame" d="M16 4c3.4 4 3.4 8.4 0 11-3.4-2.6-3.4-7 0-11z" fill="#FFB54A" stroke="#3E1A1C" stroke-width=".8"/>
    <path class="decor-candle__flame" d="M16 8c1.4 1.8 1.4 3.8 0 5-1.4-1.2-1.4-3.2 0-5z" fill="#FFF3C4"/>
    <path d="M16 15v3" stroke="#3E1A1C" stroke-width="1"/>
    <rect x="10.5" y="18" width="11" height="28" rx="1.6" fill="#FFF6E6" stroke="#3E1A1C" stroke-width="1.2"/>
    <path d="M12.5 20v16" stroke="#E9D9BE" stroke-width="2"/>
    <path d="M11 19c1 2 2 4 1.2 7" stroke="#F1E3C8" stroke-width="1.4" fill="none"/>
    <path d="M5 46h22l-3 5H8z" fill="#D9A754" stroke="#3E1A1C" stroke-width="1.2" stroke-linejoin="round"/>
    <ellipse cx="16" cy="54" rx="12" ry="4" fill="#C08A3A" stroke="#3E1A1C" stroke-width="1.2"/>
    <path d="M26 52c4 0 5 3 2 5" stroke="#3E1A1C" stroke-width="1.2" fill="none"/>
</svg>
