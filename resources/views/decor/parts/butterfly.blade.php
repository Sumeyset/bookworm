{{-- A monarch butterfly (48×36) --}}
@php($u = 'dy' . Str::random(5))
<svg {!! $attrs ?? '' !!} viewBox="0 0 48 36" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
    <defs><linearGradient id="{{ $u }}" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#FFBE55"/><stop offset=".6" stop-color="#F2801C"/><stop offset="1" stop-color="#C4520C"/></linearGradient></defs>
    <g class="tree-butterfly__wings">
        <path d="M24 17C18 2 4 0 3 9s9 11 21 8zM24 19C17 22 8 31 11 33s11-5 13-14zM24 17c6-15 20-17 21-8s-9 11-21 8zM24 19c7 3 16 12 13 14s-11-5-13-14z" fill="url(#{{ $u }})" stroke="#2A1A10" stroke-width="1.5" stroke-linejoin="round"/>
        <path d="M23 15C17 7 10 5 6 8M23 17C15 14 9 13 5 11M25 15c6-8 13-10 17-7M25 17c8-3 14-4 18-6M23 20c-4 4-8 8-10 11M25 20c4 4 8 8 10 11" stroke="#2A1A10" stroke-width=".9" fill="none" opacity=".75"/>
        <g fill="#fff"><circle cx="5" cy="8" r="1.1"/><circle cx="8" cy="5" r=".8"/><circle cx="43" cy="8" r="1.1"/><circle cx="40" cy="5" r=".8"/><circle cx="12" cy="31" r=".8"/><circle cx="36" cy="31" r=".8"/></g>
    </g>
    <ellipse cx="24" cy="19" rx="1.9" ry="8" fill="#2A1A10"/>
    <path d="M23 11c-2-4-4-6-6-7M25 11c2-4 4-6 6-7" stroke="#2A1A10" stroke-width="1" fill="none" stroke-linecap="round"/>
</svg>
