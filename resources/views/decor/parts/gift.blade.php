{{-- A wrapped present (64×60). $tone: wine|forest|cream --}}
@php
    $u = 'dg' . Str::random(5);
    [$top, $bottom, $ribbon, $ribbonDark] = ['wine' => ['#C24A61', '#7E2236', '#F4C77E', '#C9963E'], 'forest' => ['#56A57C', '#245A41', '#FFF6E6', '#D9C7A8'], 'cream' => ['#FFF2D6', '#E5C285', '#B23A52', '#7E2236']][$tone ?? 'wine'];
@endphp
<svg {!! $attrs ?? '' !!} viewBox="0 0 64 60" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs><linearGradient id="{{ $u }}" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="{{ $top }}"/><stop offset="1" stop-color="{{ $bottom }}"/></linearGradient></defs>
    <ellipse cx="32" cy="58" rx="27" ry="2.4" fill="#000" opacity=".2"/>
    <rect x="7" y="25" width="50" height="32" rx="2.5" fill="url(#{{ $u }})" stroke="#3E1A1C" stroke-width="1.5"/>
    <rect x="4" y="17.5" width="56" height="10" rx="2.5" fill="url(#{{ $u }})" stroke="#3E1A1C" stroke-width="1.5"/>
    <path d="M8 29h48" stroke="#000" stroke-opacity=".14" stroke-width="2"/>
    <rect x="28" y="17.5" width="8" height="39.5" fill="{{ $ribbon }}" stroke="#3E1A1C" stroke-width="1.2"/>
    <path d="M32 17.5c-4.5-11-18-11-15.5-2.6 1.2 4 9.5 4 15.5 2.6zM32 17.5c4.5-11 18-11 15.5-2.6-1.2 4-9.5 4-15.5 2.6z" fill="{{ $ribbon }}" stroke="#3E1A1C" stroke-width="1.3" stroke-linejoin="round"/>
    <path d="M22 11.5c3 .6 6.5 2.6 8.5 5M42 11.5c-3 .6-6.5 2.6-8.5 5" stroke="{{ $ribbonDark }}" stroke-width="1.2" fill="none"/>
    <circle cx="32" cy="17.5" r="3.4" fill="{{ $ribbon }}" stroke="#3E1A1C" stroke-width="1.2"/>
    <path d="M10 28v26" stroke="#fff" stroke-width="2" opacity=".25" stroke-linecap="round"/>
</svg>
