{{-- A potted plant with trailing leaves (64×84) --}}
@php($u = 'dpl' . Str::random(5))
<svg {!! $attrs ?? '' !!} viewBox="0 0 64 84" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs><linearGradient id="{{ $u }}" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#E08B63"/><stop offset="1" stop-color="#A9533A"/></linearGradient></defs>
    <ellipse cx="32" cy="82" rx="20" ry="2.2" fill="#000" opacity=".2"/>
    <g stroke="#3E1A1C" stroke-width="1.2" stroke-linejoin="round">
        <path d="M30 44C22 30 10 26 4 30c6 4 14 8 26 14z" fill="#6E9A4A"/>
        <path d="M34 44c8-16 20-20 26-16-6 4-14 10-26 16z" fill="#7FAE57"/>
        <path d="M32 44C30 26 34 12 42 6c2 10-2 26-10 38z" fill="#5E8A3A"/>
        <path d="M32 44C28 30 20 18 12 14c0 10 8 22 20 30z" fill="#8DBA63"/>
        <path d="M24 50c-6 6-12 16-12 26 4-2 8-10 12-26zM42 50c4 6 10 12 12 20-6-2-10-10-12-20z" fill="#7FAE57"/>
        <path d="M14 48h36l-4 32H18z" fill="url(#{{ $u }})"/>
        <rect x="11" y="44" width="42" height="8" rx="2" fill="#C9734F"/>
    </g>
    <path d="M17 56h30" stroke="#fff" stroke-width="1.6" opacity=".25"/>
</svg>
