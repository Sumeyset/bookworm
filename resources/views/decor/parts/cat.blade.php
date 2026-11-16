{{-- A ginger cat curled up asleep (96×60). Its tail twitches. --}}
@php($u = 'dct' . Str::random(5))
<svg {!! $attrs ?? '' !!} viewBox="0 0 96 60" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs><radialGradient id="{{ $u }}" cx=".45" cy=".35" r=".8"><stop offset="0" stop-color="#F3A65E"/><stop offset="1" stop-color="#C7702E"/></radialGradient></defs>
    <ellipse cx="48" cy="57" rx="40" ry="3" fill="#000" opacity=".2"/>
    <path class="decor-cat__tail" d="M80 50c12 0 14-12 6-14-4-1-4 4 0 5" fill="none" stroke="#3E1A1C" stroke-width="7" stroke-linecap="round"/>
    <path class="decor-cat__tail" d="M80 50c12 0 14-12 6-14-4-1-4 4 0 5" fill="none" stroke="#E08A44" stroke-width="4.6" stroke-linecap="round"/>
    <ellipse cx="54" cy="40" rx="32" ry="17" fill="url(#{{ $u }})" stroke="#3E1A1C" stroke-width="1.5"/>
    <path d="M40 27c2 4 2 8 0 12M54 24c2 5 2 10 0 15M68 27c2 4 2 8 0 12" stroke="#B85E25" stroke-width="2.2" fill="none" stroke-linecap="round"/>
    <path d="M14 30l2-12 9 7zM34 26l-1-11-9 6z" fill="#E08A44" stroke="#3E1A1C" stroke-width="1.3" stroke-linejoin="round"/>
    <path d="M16.5 27l1.2-5.6 4 3.2z" fill="#F7B7A8"/>
    <ellipse cx="25" cy="38" rx="14" ry="12" fill="url(#{{ $u }})" stroke="#3E1A1C" stroke-width="1.5"/>
    <path d="M17 37q3 2.4 6 0M27 37q3 2.4 6 0" stroke="#3E1A1C" stroke-width="1.4" fill="none" stroke-linecap="round"/>
    <path d="M24 41.5l1.2 1.4 1.2-1.4z" fill="#C24A61"/>
    <path d="M14 42h-6M14 44.5l-6 1.4M36 42h6M36 44.5l6 1.4" stroke="#3E1A1C" stroke-width=".8"/>
    <ellipse cx="32" cy="52" rx="10" ry="5" fill="#F7D3A8" stroke="#3E1A1C" stroke-width="1.2"/>
    <g class="decor-cat__zz" fill="#8A6A55" font-family="Georgia, serif" font-weight="700"><text x="40" y="16" font-size="9">z</text><text x="47" y="9" font-size="7">z</text></g>
</svg>
