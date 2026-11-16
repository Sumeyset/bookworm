{{--
    Barnaby the worm, drawn like the worm in the Bookworm logo: a salmon worm
    with a dark outline and big round eyes, curled up on a stack of books,
    in the burgundy and gold medallion.
--}}
@php($uid = 'barnaby-' . Str::random(6))
<svg {{ $attributes->merge(['viewBox' => '0 0 64 64', 'aria-hidden' => 'true']) }} xmlns="http://www.w3.org/2000/svg">
  <defs>
    <radialGradient id="{{ $uid }}-bg" cx="35%" cy="25%" r="85%">
      <stop offset="0" stop-color="#8E2A40"/><stop offset=".6" stop-color="#6A1C2D"/><stop offset="1" stop-color="#40101C"/>
    </radialGradient>
    <linearGradient id="{{ $uid }}-body" x1="0" y1="0" x2="1" y2="0">
      <stop offset="0" stop-color="#F3A383"/><stop offset=".55" stop-color="#E5876A"/><stop offset="1" stop-color="#C96A50"/>
    </linearGradient>
    <radialGradient id="{{ $uid }}-head" cx="40%" cy="35%" r="70%">
      <stop offset="0" stop-color="#F8B497"/><stop offset=".7" stop-color="#E5876A"/><stop offset="1" stop-color="#CF7055"/>
    </radialGradient>
    <clipPath id="{{ $uid }}-circle"><circle cx="32" cy="32" r="32"/></clipPath>
  </defs>
  <g clip-path="url(#{{ $uid }}-circle)">
    <circle cx="32" cy="32" r="32" fill="url(#{{ $uid }}-bg)"/>
    <circle cx="32" cy="32" r="29.6" fill="none" stroke="#D9B26F" stroke-opacity=".75" stroke-width="1"/>
    <circle cx="13" cy="15" r="1" fill="#F2C38B" opacity=".7"/>
    <circle cx="17" cy="10" r=".6" fill="#F2C38B" opacity=".5"/>

    {{-- the stack of books, like the logo --}}
    <g stroke="#3E1A1C" stroke-width="1.3" stroke-linejoin="round">
      <rect x="9" y="52" width="40" height="8" rx="1.6" fill="#D98466"/>
      <rect x="13" y="45" width="34" height="7.4" rx="1.6" fill="#FBEFE6"/>
      <rect x="11" y="38.2" width="31" height="7.2" rx="1.6" fill="#E9B7BF" transform="rotate(-4 26 42)"/>
    </g>
    <path d="M16 41.2l14-1" stroke="#9A3049" stroke-width="2" stroke-linecap="round" transform="rotate(-4 26 42)"/>
    <path d="M40 45v7" stroke="#B8475E" stroke-width="2.2"/>
    <path d="M12 56h36" stroke="#B5634A" stroke-width=".8" opacity=".6"/>

    {{-- the worm: body curling up behind the books, then the head --}}
    <path d="M44.5 47c5.5-3 6.5-10 2-14.5-3-3-6-4.5-7-9" fill="none" stroke="#3E1A1C" stroke-width="10.4" stroke-linecap="round"/>
    <path d="M44.5 47c5.5-3 6.5-10 2-14.5-3-3-6-4.5-7-9" fill="none" stroke="url(#{{ $uid }}-body)" stroke-width="7.8" stroke-linecap="round"/>
    <path d="M45.6 43.2c1.6.6 3.2.4 4.5-.6M46.4 38.5c1.6 0 3-.6 4-1.8M44.6 34c1.4-.3 2.6-1.1 3.3-2.3M41.6 30.2c1.2-.6 2-1.6 2.3-2.8" stroke="#B85E45" stroke-width=".9" stroke-linecap="round" fill="none" opacity=".75"/>
    {{-- a little arm resting on the top book --}}
    <path d="M43.6 41.5c-1.8-.4-3.1-1.4-3.6-2.6" stroke="#3E1A1C" stroke-width="3.6" stroke-linecap="round" fill="none"/>
    <path d="M43.6 41.5c-1.8-.4-3.1-1.4-3.6-2.6" stroke="#E5876A" stroke-width="1.8" stroke-linecap="round" fill="none"/>

    <circle cx="37.5" cy="17.5" r="10.2" fill="url(#{{ $uid }}-head)" stroke="#3E1A1C" stroke-width="1.4"/>
    {{-- big round eyes, looking up and to the side --}}
    <circle cx="33.6" cy="15.6" r="3.7" fill="#fff" stroke="#3E1A1C" stroke-width="1.1"/>
    <circle cx="41.4" cy="15.6" r="3.7" fill="#fff" stroke="#3E1A1C" stroke-width="1.1"/>
    <circle cx="34.6" cy="15" r="1.75" fill="#2A1416"/>
    <circle cx="42.4" cy="15" r="1.75" fill="#2A1416"/>
    <circle cx="35.2" cy="14.3" r=".6" fill="#fff"/>
    <circle cx="43" cy="14.3" r=".6" fill="#fff"/>
    <ellipse cx="31.4" cy="21.2" rx="1.9" ry="1.1" fill="#E0566F" opacity=".45"/>
    <ellipse cx="44" cy="21.2" rx="1.9" ry="1.1" fill="#E0566F" opacity=".45"/>
    <path d="M35 21.4q2.8 2.6 5.6 0" fill="none" stroke="#3E1A1C" stroke-width="1.3" stroke-linecap="round"/>
  </g>
</svg>
