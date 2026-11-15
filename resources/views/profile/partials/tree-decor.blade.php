{{--
    Seasonal decorations layered on the tree picture. Positions are picture
    pixels (App\Support\TreeBackdrop), so they follow the tree as it grows.
    Expects $theme (christmas | autumn | night) and $branchCount.
--}}
@use('App\Support\TreeBackdrop', 'Tree')
@php
    $at = fn (array $p) => Tree::point($p[0], $p[1], $branchCount);
    [$wx1, $wy1, $wx2, $wy2] = Tree::WINDOW;
    $winTop = $at([$wx1, $wy1]);
    $winBottom = $at([$wx2, $wy2]);
    $window = 'left: ' . $winTop['left'] . '%; top: ' . $winTop['top'] . '%; width: ' . round(($wx2 - $wx1) / Tree::WIDTH * 100, 3) . '%; height: ' . round($winBottom['top'] - $winTop['top'], 3) . '%;';
    $style = fn (array $p) => 'left: ' . $at($p)['left'] . '%; top: ' . $at($p)['top'] . '%;';
    // A steady pseudo-random spread for falling things and fireflies.
    $spread = fn (int $i, int $salt) => (($i * 37 + $salt * 53) % 97) / 97;
@endphp

<div class="tree-decor" aria-hidden="true">
    {{-- What you see through the window --}}
    <span class="tree-decor__window tree-decor__window--{{ $theme }}" style="{{ $window }}">
        @if ($theme === 'night')
            <svg class="tree-decor__moon" viewBox="0 0 40 40"><circle cx="20" cy="20" r="14" fill="#FFF6D8"/><circle cx="26" cy="15" r="12" fill="#1B2448"/></svg>
        @endif
    </span>

    @if ($theme === 'christmas')
        {{-- Star on top --}}
        <svg class="tree-decor__star" style="{{ $style(Tree::CROWN_TOP) }}" viewBox="0 0 48 48">
            <defs><radialGradient id="tree-star-glow"><stop offset="0" stop-color="#FFE7A3" stop-opacity=".9"/><stop offset="1" stop-color="#FFE7A3" stop-opacity="0"/></radialGradient></defs>
            <circle cx="24" cy="24" r="24" fill="url(#tree-star-glow)"/>
            <path d="M24 7l4.7 10.6 11.5 1.1-8.7 7.7 2.5 11.3L24 31.8l-10 5.9 2.5-11.3-8.7-7.7 11.5-1.1z" fill="#F4C64E" stroke="#C9922A" stroke-width="1.2" stroke-linejoin="round"/>
            <path d="M24 11l3 7" stroke="#FFF3C4" stroke-width="1.6" stroke-linecap="round" opacity=".8"/>
        </svg>

        {{-- Baubles hanging in the leaves --}}
        @foreach (Tree::ORNAMENTS as $i => $point)
            <span class="tree-ornament tree-ornament--{{ ['red', 'gold', 'green', 'blue'][$i % 4] }}" style="{{ $style($point) }} --sway: {{ 2.6 + ($i % 4) * 0.4 }}s; --delay: -{{ $i * 0.7 }}s;"></span>
        @endforeach

        {{-- Presents by the roots --}}
        <svg class="tree-decor__gift" style="{{ $style(Tree::FLOOR_LEFT) }}" viewBox="0 0 60 50">
            <rect x="4" y="16" width="52" height="34" rx="2" fill="#8B2A3D"/><rect x="2" y="10" width="56" height="10" rx="2" fill="#A3324A"/>
            <rect x="26" y="10" width="8" height="40" fill="#F2C38B"/><path d="M30 10c-6-9-16-6-12 0M30 10c6-9 16-6 12 0" fill="none" stroke="#F2C38B" stroke-width="3" stroke-linecap="round"/>
        </svg>
        <svg class="tree-decor__gift tree-decor__gift--small" style="{{ $style([Tree::FLOOR_LEFT[0] + 70, Tree::FLOOR_LEFT[1]]) }}" viewBox="0 0 60 50">
            <rect x="6" y="18" width="48" height="32" rx="2" fill="#2F6B4F"/><rect x="4" y="12" width="52" height="9" rx="2" fill="#3C8562"/>
            <rect x="26" y="12" width="8" height="38" fill="#FFF8EF"/><path d="M30 12c-5-8-14-5-10 0M30 12c5-8 14-5 10 0" fill="none" stroke="#FFF8EF" stroke-width="3" stroke-linecap="round"/>
        </svg>
        <svg class="tree-decor__gift" style="{{ $style(Tree::FLOOR_RIGHT) }}" viewBox="0 0 60 50">
            <rect x="4" y="16" width="52" height="34" rx="2" fill="#E9C27D"/><rect x="2" y="10" width="56" height="10" rx="2" fill="#F2D49A"/>
            <rect x="26" y="10" width="8" height="40" fill="#8B2A3D"/><path d="M30 10c-6-9-16-6-12 0M30 10c6-9 16-6 12 0" fill="none" stroke="#8B2A3D" stroke-width="3" stroke-linecap="round"/>
        </svg>
        <span class="tree-decor__floor tree-decor__floor--snow"></span>
    @elseif ($theme === 'autumn')
        {{-- Fallen leaves around the roots, and a pumpkin --}}
        @foreach ([[560, 924, -30, 'orange'], [610, 932, 40, 'red'], [690, 928, 10, 'gold'], [760, 934, -60, 'orange'], [960, 930, 25, 'red'], [1040, 926, -15, 'gold'], [1100, 934, 70, 'orange'], [505, 934, 80, 'gold'], [1160, 928, -45, 'red']] as [$x, $y, $turn, $tone])
            <svg class="tree-leaf tree-leaf--{{ $tone }} tree-leaf--fallen" style="{{ $style([$x, $y]) }} --turn: {{ $turn }}deg;" viewBox="0 0 24 24"><path d="M12 2C6.5 6 4.5 12 6.5 17.5 8.5 21 12 22 12 22s3.5-1 5.5-4.5C19.5 12 17.5 6 12 2z" fill="currentColor"/><path d="M12 5v17M12 11l-3-2M12 15l3-2" stroke="rgb(0 0 0 / 22%)" stroke-width="1" fill="none"/></svg>
        @endforeach
        <svg class="tree-decor__pumpkin" style="{{ $style(Tree::FLOOR_RIGHT) }}" viewBox="0 0 64 52">
            <path d="M32 10c-2-6 2-9 6-8" fill="none" stroke="#5B6B2F" stroke-width="4" stroke-linecap="round"/>
            <ellipse cx="20" cy="32" rx="14" ry="18" fill="#D9771F"/><ellipse cx="44" cy="32" rx="14" ry="18" fill="#D9771F"/>
            <ellipse cx="32" cy="32" rx="14" ry="19" fill="#EE8E2E"/>
            <path d="M32 14v36M22 15c-3 10-3 24 0 34M42 15c3 10 3 24 0 34" stroke="#B85F14" stroke-width="1.6" fill="none" opacity=".7"/>
        </svg>
        <svg class="tree-decor__pumpkin tree-decor__pumpkin--small" style="{{ $style([Tree::FLOOR_LEFT[0] + 30, Tree::FLOOR_LEFT[1]]) }}" viewBox="0 0 64 52">
            <path d="M32 10c-2-6 2-9 6-8" fill="none" stroke="#5B6B2F" stroke-width="4" stroke-linecap="round"/>
            <ellipse cx="22" cy="33" rx="13" ry="16" fill="#E2A23A"/><ellipse cx="42" cy="33" rx="13" ry="16" fill="#E2A23A"/><ellipse cx="32" cy="33" rx="13" ry="17" fill="#F0B84C"/>
        </svg>
        <span class="tree-decor__floor tree-decor__floor--leaves"></span>
    @elseif ($theme === 'night')
        {{-- Little lanterns hanging from the branches --}}
        @foreach ([[425, 200], [1185, 160], [1245, 450], [380, 330], [1235, 600], [700, 80]] as $i => $point)
            <span class="tree-lantern" style="{{ $style($point) }} --delay: -{{ $i * 1.3 }}s;"></span>
        @endforeach
    @endif
</div>

{{-- Moving things on top: snowfall, drifting leaves, fireflies --}}
<div class="tree-fx tree-fx--{{ $theme }}" aria-hidden="true">
    @if ($theme === 'christmas')
        <span class="tree-fx__snow"></span>
        <span class="tree-fx__snow tree-fx__snow--far"></span>
    @elseif ($theme === 'autumn')
        @foreach (range(0, 9) as $i)
            <span class="tree-fall" style="left: {{ round(24 + $spread($i, 1) * 56, 2) }}%; --dur: {{ round(11 + $spread($i, 2) * 8, 1) }}s; --delay: -{{ round($spread($i, 3) * 18, 1) }}s; --drift: {{ round(-40 + $spread($i, 4) * 80) }}px;">
                <svg class="tree-leaf tree-leaf--{{ ['orange', 'red', 'gold'][$i % 3] }}" viewBox="0 0 24 24"><path d="M12 2C6.5 6 4.5 12 6.5 17.5 8.5 21 12 22 12 22s3.5-1 5.5-4.5C19.5 12 17.5 6 12 2z" fill="currentColor"/><path d="M12 5v17" stroke="rgb(0 0 0 / 22%)" stroke-width="1"/></svg>
            </span>
        @endforeach
    @elseif ($theme === 'night')
        @foreach (range(0, 15) as $i)
            <span class="tree-firefly" style="left: {{ round(22 + $spread($i, 5) * 56, 2) }}%; top: {{ round(6 + $spread($i, 6) * 70, 2) }}%; --dur: {{ round(5 + $spread($i, 7) * 6, 1) }}s; --delay: -{{ round($spread($i, 8) * 9, 1) }}s;"></span>
        @endforeach
    @endif
</div>
