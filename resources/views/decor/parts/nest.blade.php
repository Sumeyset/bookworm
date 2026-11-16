{{-- A robin in its woven nest, with a blue egg (100×78) --}}
@php($u = 'dn' . Str::random(5))
<svg {!! $attrs ?? '' !!} viewBox="0 0 100 78" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs><linearGradient id="{{ $u }}" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#A97B4D"/><stop offset="1" stop-color="#6E4A2A"/></linearGradient></defs>
    <ellipse cx="50" cy="74.5" rx="38" ry="3.2" fill="#000" opacity=".2"/>
    <g class="tree-nest__bird">
        <path d="M66 36c10-3 18-1 24 4-8 2.4-16 2-23 0z" fill="#4A3020" stroke="#3E1A1C" stroke-width="1.2" stroke-linejoin="round"/>
        <ellipse cx="54" cy="38" rx="19" ry="15" fill="#7A5638" stroke="#3E1A1C" stroke-width="1.4"/>
        <path d="M58 30c6 1 10 5 11.5 9.5" stroke="#5A3B26" stroke-width="1.6" fill="none"/>
        <circle cx="40" cy="26" r="11" fill="#7A5638" stroke="#3E1A1C" stroke-width="1.4"/>
        <path d="M30 30.5c3 11 16 15 25.5 9-5-7.5-15.5-10.5-25.5-9z" fill="#EC6A34" stroke="#3E1A1C" stroke-width="1.2" stroke-linejoin="round"/>
        <path d="M33.5 33c4 5.6 10.5 7.5 16 5.6" stroke="#FFA266" stroke-width="1.4" fill="none"/>
        <circle cx="37" cy="23" r="2.6" fill="#1F1012"/><circle cx="37.9" cy="22.1" r=".9" fill="#fff"/>
        <path d="M29.5 24.5l-8.5 2.6 8.5 2.2z" fill="#F0B83C" stroke="#3E1A1C" stroke-width="1" stroke-linejoin="round"/>
    </g>
    <ellipse cx="72" cy="45" rx="5" ry="6" fill="#9FD3E6" stroke="#3E1A1C" stroke-width="1.1"/>
    <ellipse cx="70.6" cy="43" rx="1.6" ry="2.1" fill="#fff" opacity=".65"/>
    <path d="M8 46c6 22 78 22 84 0-8 7-76 7-84 0z" fill="url(#{{ $u }})" stroke="#3E1A1C" stroke-width="1.5" stroke-linejoin="round"/>
    <path d="M7 46c9-3.6 77-3.6 86 0" stroke="#C49A66" stroke-width="3.2" fill="none" stroke-linecap="round"/>
    <path d="M12 50c12 10 64 10 76 0M16 56c12 7.6 56 7.6 68 0M23 62c12 5 42 5 54 0" stroke="#4E321C" stroke-width="1.8" fill="none" stroke-linecap="round"/>
    <path d="M12 44l8 7M26 42l2 9M74 42l-3 9M88 44l-8 7M46 43l1 9M58 43l-1 9M4 47l7-2M96 47l-7-2" stroke="#C49A66" stroke-width="1.8" stroke-linecap="round"/>
</svg>
