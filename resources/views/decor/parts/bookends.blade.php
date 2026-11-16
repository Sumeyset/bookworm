{{-- Brass bookends holding three little books (80×56) --}}
@php($u = 'dbe' . Str::random(5))
<svg {!! $attrs ?? '' !!} viewBox="0 0 80 56" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs><linearGradient id="{{ $u }}" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#F5D58A"/><stop offset=".5" stop-color="#C99A45"/><stop offset="1" stop-color="#8A6224"/></linearGradient></defs>
    <ellipse cx="40" cy="54" rx="36" ry="2.4" fill="#000" opacity=".2"/>
    <g stroke="#3E1A1C" stroke-width="1.2" stroke-linejoin="round">
        <rect x="22" y="14" width="11" height="38" rx="1.4" fill="#2F5D48"/>
        <rect x="33" y="10" width="13" height="42" rx="1.4" fill="#7A2133"/>
        <rect x="46" y="18" width="10" height="34" rx="1.4" fill="#C99A45" transform="rotate(8 51 52)"/>
        <path d="M4 52V18c0-6 4-10 10-10h4v44z" fill="url(#{{ $u }})"/>
        <path d="M76 52V18c0-6-4-10-10-10h-4v44z" fill="url(#{{ $u }})"/>
        <rect x="2" y="50" width="20" height="4" rx="1" fill="#8A6224"/>
        <rect x="58" y="50" width="20" height="4" rx="1" fill="#8A6224"/>
    </g>
    <path d="M24.5 20h6M24.5 44h6M35.5 16h8M35.5 46h8" stroke="#F2D49A" stroke-width="1.2"/>
    <circle cx="11" cy="22" r="3" fill="none" stroke="#FFF1C2" stroke-width="1.2" opacity=".8"/><circle cx="69" cy="22" r="3" fill="none" stroke="#FFF1C2" stroke-width="1.2" opacity=".8"/>
</svg>
