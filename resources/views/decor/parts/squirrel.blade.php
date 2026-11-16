{{-- A red squirrel holding an acorn (90×110) --}}
@php($u = 'dq' . Str::random(5))
<svg {!! $attrs ?? '' !!} viewBox="0 0 90 110" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        <linearGradient id="{{ $u }}t" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#E2955A"/><stop offset="1" stop-color="#9A4D1E"/></linearGradient>
        <radialGradient id="{{ $u }}b" cx=".4" cy=".3" r=".8"><stop offset="0" stop-color="#D07A3E"/><stop offset="1" stop-color="#A45424"/></radialGradient>
    </defs>
    <path class="tree-squirrel__tail" d="M50 76c32 2 42-36 22-58C61 6 43 10 45 25c2 11 16 9 14 21-2 9-16 13-9 30z" fill="url(#{{ $u }}t)" stroke="#3E1A1C" stroke-width="1.5" stroke-linejoin="round"/>
    <path class="tree-squirrel__tail" d="M57 22c10-1 16 7 14 17M61 33c6 1 8 6 7 11" stroke="#F4B57A" stroke-width="2.4" fill="none" stroke-linecap="round"/>
    <ellipse cx="34" cy="70" rx="16" ry="24" fill="url(#{{ $u }}b)" stroke="#3E1A1C" stroke-width="1.5"/>
    <ellipse cx="29" cy="74" rx="8" ry="15" fill="#F5DCB8"/>
    <path d="M24 31l-3.4-11.4 8.6 6.4zM37 29l3-11.4 4.6 9.4z" fill="#C46A30" stroke="#3E1A1C" stroke-width="1.3" stroke-linejoin="round"/>
    <circle cx="32" cy="40" r="13.4" fill="url(#{{ $u }}b)" stroke="#3E1A1C" stroke-width="1.5"/>
    <ellipse cx="23.6" cy="44.4" rx="7.2" ry="6" fill="#F5DCB8"/>
    <circle cx="27.4" cy="37.6" r="2.8" fill="#1F1012"/><circle cx="28.3" cy="36.7" r="1" fill="#fff"/>
    <circle cx="17.6" cy="44" r="2" fill="#3A2214"/>
    <path d="M18.4 47.4c2 1.6 4.4 1.6 6.4 0" stroke="#3A2214" stroke-width="1.1" fill="none" stroke-linecap="round"/>
    <ellipse cx="22" cy="61" rx="6.2" ry="7.2" fill="#9A642F" stroke="#3E1A1C" stroke-width="1.2"/>
    <path d="M16 57.4c2-4.4 10-4.4 12 0z" fill="#5A3A1E" stroke="#3E1A1C" stroke-width="1.1" stroke-linejoin="round"/>
    <path d="M22 53.2v-2.8" stroke="#3E1A1C" stroke-width="1.4" stroke-linecap="round"/>
    <path d="M26.5 58.4c-3 3.2-7.4 3.2-9.6 0M28.6 64.6c-3 3.2-8.4 3.2-10.6 0" stroke="#A45424" stroke-width="3.6" stroke-linecap="round" fill="none"/>
    <path d="M24 91l-8 8M38 93l3 10" stroke="#A45424" stroke-width="4.8" stroke-linecap="round"/>
</svg>
