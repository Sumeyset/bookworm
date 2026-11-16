{{-- Christmas star (64×64). $attrs: extra attributes. --}}
@php($u = 'ds' . Str::random(5))
<svg {!! $attrs ?? '' !!} viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        <radialGradient id="{{ $u }}g"><stop offset="0" stop-color="#FFE7A3" stop-opacity=".9"/><stop offset=".5" stop-color="#FFD36B" stop-opacity=".3"/><stop offset="1" stop-color="#FFD36B" stop-opacity="0"/></radialGradient>
        <linearGradient id="{{ $u }}s" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#FFF1B8"/><stop offset=".5" stop-color="#F6C445"/><stop offset="1" stop-color="#D9952A"/></linearGradient>
    </defs>
    <circle cx="32" cy="32" r="31" fill="url(#{{ $u }}g)"/>
    <path d="M32 8.5l6.6 14.6 15.9 1.6-12 10.6 3.5 15.6L32 43l-14 7.9 3.5-15.6-12-10.6 15.9-1.6z" fill="url(#{{ $u }}s)" stroke="#3E1A1C" stroke-width="1.6" stroke-linejoin="round"/>
    <path d="M32 13l5 11.2M32 13l-5 11.2" stroke="#FFF6D2" stroke-width="1.4" stroke-linecap="round" opacity=".8"/>
    <path d="M32 32l.1 10M32 32l-9.6 3.5M32 32l9.6 3.5" stroke="#C98A26" stroke-width="1" opacity=".45"/>
    <g fill="#FFF8E0"><path d="M53 9l1 2.3 2.3 1-2.3 1L53 15.6l-1-2.3-2.3-1 2.3-1z"/><circle cx="11" cy="16" r="1.2"/><circle cx="56" cy="44" r="1"/></g>
</svg>
