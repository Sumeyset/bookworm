{{-- A wooden swing on twisted ropes, with flowers (100×200). Hangs from the top. --}}
@php($u = 'dw' . Str::random(5))
<svg {!! $attrs ?? '' !!} viewBox="0 0 100 200" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs><linearGradient id="{{ $u }}" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#D19A62"/><stop offset="1" stop-color="#84552E"/></linearGradient></defs>
    <g class="tree-swing">
        <path d="M18 0v166M82 0v166" stroke="#3E1A1C" stroke-width="4.6"/>
        <path d="M18 0v166M82 0v166" stroke="#D2AD72" stroke-width="2.8"/>
        <path d="M18 0v166M82 0v166" stroke="#8A6A3A" stroke-width="2.8" stroke-dasharray="2.2 3.6"/>
        <g stroke="#3E1A1C" stroke-width=".8"><circle cx="14.6" cy="58" r="3.2" fill="#F7B7C6"/><circle cx="21" cy="62" r="3.2" fill="#F7B7C6"/><circle cx="15.6" cy="65" r="3.2" fill="#F7B7C6"/></g>
        <circle cx="17.6" cy="61.8" r="1.8" fill="#F4C64E"/>
        <g stroke="#3E1A1C" stroke-width=".8"><circle cx="78.6" cy="98" r="3.2" fill="#FFF0F4"/><circle cx="85" cy="102" r="3.2" fill="#FFF0F4"/><circle cx="79.6" cy="105" r="3.2" fill="#FFF0F4"/></g>
        <circle cx="81.6" cy="101.8" r="1.8" fill="#F4C64E"/>
        <path d="M21.5 64c3-3.4 6.4-2.4 8.6 1M76.5 100c-3-3.4-6.4-2.4-8.6 1" stroke="#5E8A3A" stroke-width="1.8" fill="none" stroke-linecap="round"/>
        <rect x="4" y="162" width="92" height="15" rx="4" fill="url(#{{ $u }})" stroke="#3E1A1C" stroke-width="1.6"/>
        <path d="M11 168h30M48 172h38" stroke="#5A3A1E" stroke-width="1" opacity=".55"/>
        <rect x="6" y="163.4" width="88" height="3.4" rx="1.7" fill="#F0C48F" opacity=".8"/>
        <circle cx="18" cy="165" r="3.6" fill="#8A6A3A" stroke="#3E1A1C" stroke-width="1"/><circle cx="82" cy="165" r="3.6" fill="#8A6A3A" stroke="#3E1A1C" stroke-width="1"/>
    </g>
</svg>
