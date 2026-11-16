{{-- A hanging bronze lantern with a candle (40×74) --}}
@php($u = 'dl' . Str::random(5))
<svg {!! $attrs ?? '' !!} viewBox="0 0 40 74" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        <radialGradient id="{{ $u }}g" cx=".5" cy=".55" r=".5"><stop offset="0" stop-color="#FFE7A3" stop-opacity=".9"/><stop offset=".55" stop-color="#FFC861" stop-opacity=".3"/><stop offset="1" stop-color="#FFC861" stop-opacity="0"/></radialGradient>
        <linearGradient id="{{ $u }}b" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#4A2E18"/><stop offset=".5" stop-color="#9C6A37"/><stop offset="1" stop-color="#4A2E18"/></linearGradient>
        <linearGradient id="{{ $u }}c" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#FFF7DA"/><stop offset=".6" stop-color="#FFCB66"/><stop offset="1" stop-color="#EC9235"/></linearGradient>
    </defs>
    <path d="M20 0v7" stroke="#6B4A2A" stroke-width="1.2"/>
    <circle cx="20" cy="9.5" r="3" fill="none" stroke="#3E1A1C" stroke-width="2"/>
    <ellipse cx="20" cy="42" rx="20" ry="24" fill="url(#{{ $u }}g)"/>
    <path d="M8 21h24l-4-7.5H12z" fill="url(#{{ $u }}b)" stroke="#3E1A1C" stroke-width="1.3" stroke-linejoin="round"/>
    <rect x="7" y="20.5" width="26" height="3.5" rx="1.2" fill="#5A3A1E" stroke="#3E1A1C" stroke-width="1.1"/>
    <path d="M9.5 24h21l-2.4 33H11.9z" fill="url(#{{ $u }}c)" stroke="#3E1A1C" stroke-width="1.3" stroke-linejoin="round"/>
    <path d="M15.5 24l-.8 33M24.5 24l.8 33" stroke="#3E1A1C" stroke-width="1.6"/>
    <rect x="17.4" y="41" width="5.2" height="12" rx="1" fill="#FFF8EA" stroke="#C99A55" stroke-width=".8"/>
    <path d="M20 31c3.2 4.3 3.2 8.5 0 10.6-3.2-2.1-3.2-6.3 0-10.6z" fill="#FFF7D6"/>
    <path d="M20 35c1.5 2 1.5 4.3 0 5.3-1.5-1-1.5-3.3 0-5.3z" fill="#FF9A3C"/>
    <rect x="8.4" y="57" width="23.2" height="4.4" rx="1.6" fill="url(#{{ $u }}b)" stroke="#3E1A1C" stroke-width="1.1"/>
    <path d="M13 61.4h14l-2.2 4.4h-9.6z" fill="#4A2E18" stroke="#3E1A1C" stroke-width="1"/>
    <path d="M11.2 26.5l1 28" stroke="#fff" stroke-width="1.6" opacity=".45" stroke-linecap="round"/>
</svg>
