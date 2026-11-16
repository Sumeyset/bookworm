{{-- A brown owl that blinks (80×100). Feet at the bottom centre. --}}
@php($u = 'do' . Str::random(5))
<svg {!! $attrs ?? '' !!} viewBox="0 0 80 100" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs>
        <radialGradient id="{{ $u }}b" cx=".5" cy=".3" r=".85"><stop offset="0" stop-color="#B08458"/><stop offset="1" stop-color="#6A4529"/></radialGradient>
        <linearGradient id="{{ $u }}w" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#7A5233"/><stop offset="1" stop-color="#4A2F1C"/></linearGradient>
    </defs>
    <ellipse cx="40" cy="97" rx="24" ry="2.8" fill="#000" opacity=".22"/>
    <path d="M17 27L20 6l13 13M63 27L60 6 47 19" fill="#6A4529" stroke="#3E1A1C" stroke-width="1.5" stroke-linejoin="round"/>
    <ellipse cx="40" cy="56" rx="27" ry="35" fill="url(#{{ $u }}b)" stroke="#3E1A1C" stroke-width="1.6"/>
    <path d="M14.5 48c-6.5 15-3.5 31 8 39 1.5-15-1.5-29-8-39zM65.5 48c6.5 15 3.5 31-8 39-1.5-15 1.5-29 8-39z" fill="url(#{{ $u }}w)" stroke="#3E1A1C" stroke-width="1.4" stroke-linejoin="round"/>
    <path d="M17 60c2.5 5 3.4 12 3.4 19M63 60c-2.5 5-3.4 12-3.4 19" stroke="#9B734E" stroke-width="1.2" fill="none"/>
    <ellipse cx="40" cy="67" rx="16.5" ry="21" fill="#F0DDB6" stroke="#3E1A1C" stroke-width="1.2"/>
    <path d="M30 59q3 3.4 6 0M38 63q3 3.4 6 0M44 57q3 3.4 6 0M32 69q3 3.4 6 0M41 73q3 3.4 6 0M35 79q3 3.4 6 0M44 82q3 3.4 6 0" stroke="#A8825A" stroke-width="1.3" fill="none"/>
    <path d="M14 36c4-13 22-17 26-7 4-10 22-6 26 7-6 11-20 13-26 5-6 8-20 6-26-5z" fill="#F7E8CB" stroke="#3E1A1C" stroke-width="1.3" stroke-linejoin="round"/>
    <g class="tree-owl__eyes">
        <circle cx="28" cy="34" r="8.6" fill="#F6B73C" stroke="#3E1A1C" stroke-width="1.3"/>
        <circle cx="52" cy="34" r="8.6" fill="#F6B73C" stroke="#3E1A1C" stroke-width="1.3"/>
        <circle cx="28.6" cy="34.2" r="5" fill="#1F1012"/>
        <circle cx="52.6" cy="34.2" r="5" fill="#1F1012"/>
        <circle cx="30.4" cy="32" r="1.8" fill="#fff"/><circle cx="54.4" cy="32" r="1.8" fill="#fff"/>
    </g>
    <path d="M40 38.5l-4.4 6.4h8.8z" fill="#E9A43A" stroke="#3E1A1C" stroke-width="1.1" stroke-linejoin="round"/>
    <path d="M28 92l-4 5M33 93v5M47 93v5M52 92l4 5" stroke="#E9A43A" stroke-width="2.8" stroke-linecap="round"/>
</svg>
