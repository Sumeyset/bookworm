{{-- Barnaby the worm: a bookish little worm with round glasses, peeking out of an open book. --}}
@php($uid = 'barnaby-' . Str::random(6))
<svg {{ $attributes->merge(['viewBox' => '0 0 64 64', 'aria-hidden' => 'true']) }} xmlns="http://www.w3.org/2000/svg">
    <defs>
        <linearGradient id="{{ $uid }}-bg" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#8B2A3D"/><stop offset="1" stop-color="#4A1220"/></linearGradient>
        <linearGradient id="{{ $uid }}-body" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#F7B5C2"/><stop offset="1" stop-color="#E38599"/></linearGradient>
    </defs>
    <circle cx="32" cy="32" r="32" fill="url(#{{ $uid }}-bg)"/>
    {{-- body rising out of the book --}}
    <path d="M27 50c-1-7 1-12 5-15 4-3 5-7 3-11" fill="none" stroke="url(#{{ $uid }}-body)" stroke-width="11" stroke-linecap="round"/>
    <path d="M25.8 44.5h4.6M27.5 38.8h4.3M31.6 33.6h4.2" stroke="#C9677D" stroke-width="1.4" stroke-linecap="round" opacity=".7"/>
    {{-- head --}}
    <circle cx="35" cy="21" r="9.5" fill="url(#{{ $uid }}-body)"/>
    <circle cx="30.4" cy="25" r="1.8" fill="#F28AA0" opacity=".7"/>
    <circle cx="40.6" cy="24.6" r="1.8" fill="#F28AA0" opacity=".7"/>
    {{-- glasses --}}
    <circle cx="31.5" cy="20" r="3.6" fill="#FFF8EF" fill-opacity=".55" stroke="#2A1A1D" stroke-width="1.3"/>
    <circle cx="39.2" cy="19.7" r="3.6" fill="#FFF8EF" fill-opacity=".55" stroke="#2A1A1D" stroke-width="1.3"/>
    <path d="M35.1 19.8h0.5" stroke="#2A1A1D" stroke-width="1.3" stroke-linecap="round"/>
    <circle cx="32" cy="20.3" r="1.25" fill="#2A1A1D"/>
    <circle cx="39.6" cy="20" r="1.25" fill="#2A1A1D"/>
    <path d="M33.3 25.6c1.2 1 2.8 1 4 0" fill="none" stroke="#2A1A1D" stroke-width="1.2" stroke-linecap="round"/>
    {{-- a little tuft --}}
    <path d="M35.5 11.6c.2-1.8 1.4-3 3-3.2" fill="none" stroke="#E38599" stroke-width="1.6" stroke-linecap="round"/>
    {{-- the open book --}}
    <path d="M12 46c6-2.4 13-2.4 20 1 7-3.4 14-3.4 20-1v8c-6-2.4-13-2.4-20 1-7-3.4-14-3.4-20-1z" fill="#FFF8EF"/>
    <path d="M32 47v8" stroke="#D9C6B6" stroke-width="1.2"/>
    <path d="M16 49.4c3.6-1 7.4-.9 11 .3M37 49.7c3.6-1.2 7.4-1.3 11-.3" stroke="#D9C6B6" stroke-width="1" stroke-linecap="round"/>
</svg>
