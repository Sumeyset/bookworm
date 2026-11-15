{{--
    Decorations from the Acorn shop, layered on a shelf picture.
    Expects $decorations (list of item keys) and either
      - $branchCount: the living tree; spots come from App\Support\TreeBackdrop
        (picture pixels, so they follow the tree as it grows), or
      - $spots: ready-made positions from App\Support\ShelfScene for a photo shelf.
    $windowSnow: snow falls only outside the window (window nook), not in the room.
--}}
@use('App\Support\TreeBackdrop', 'Tree')
@php
    $on = fn (string $item) => in_array($item, $decorations, true);
    $windowSnow = $windowSnow ?? false;
    if (! isset($spots)) {
        $at = fn (array $p) => Tree::point($p[0], $p[1], $branchCount);
        [$wx1, $wy1, $wx2, $wy2] = Tree::WINDOW;
        $winTop = $at([$wx1, $wy1]);
        $winBottom = $at([$wx2, $wy2]);
        $spots = [
            'star' => $at(Tree::CROWN_TOP),
            'ornaments' => array_map($at, Tree::ORNAMENTS),
            'gifts' => [$at(Tree::FLOOR_LEFT), $at([Tree::FLOOR_LEFT[0] + 70, Tree::FLOOR_LEFT[1]]), $at(Tree::FLOOR_RIGHT)],
            'pumpkins' => [$at(Tree::FLOOR_RIGHT), $at([Tree::FLOOR_LEFT[0] + 30, Tree::FLOOR_LEFT[1]])],
            'leaves' => array_map(fn ($l) => ['at' => $at([$l[0], $l[1]]), 'turn' => $l[2]], [[560, 924, -30], [610, 932, 40], [690, 928, 10], [760, 934, -60], [960, 930, 25], [1040, 926, -15], [1100, 934, 70], [505, 934, 80], [1160, 928, -45]]),
            'lanterns' => array_map($at, Tree::LANTERNS),
            'nest' => $at(Tree::NEST),
            'owl' => $at(Tree::OWL),
            'swing' => $at(Tree::SWING),
            'squirrel' => $at(Tree::SQUIRREL),
            'butterflies' => array_map($at, Tree::BUTTERFLIES),
            'window' => 'left: ' . $winTop['left'] . '%; top: ' . $winTop['top'] . '%; width: ' . round(($wx2 - $wx1) / Tree::WIDTH * 100, 3) . '%; height: ' . round($winBottom['top'] - $winTop['top'], 3) . '%;',
        ];
    }
    $style = fn (array $p) => 'left: ' . $p['left'] . '%; top: ' . $p['top'] . '%;';
    $windowTheme = empty($spots['window']) ? null : ($on('evening') ? 'night' : ($on('snow') ? 'christmas' : ($on('autumn') ? 'autumn' : null)));
    $tones = ['orange', 'red', 'gold'];
    // A steady pseudo-random spread for falling things and fireflies.
    $spread = fn (int $i, int $salt) => (($i * 37 + $salt * 53) % 97) / 97;
@endphp

@if ($decorations)
<div class="tree-decor" aria-hidden="true">
    @if ($windowTheme)
        <span class="tree-decor__window tree-decor__window--{{ $windowTheme }}" style="{{ $spots['window'] }}">
            @if ($windowTheme === 'night')
                <svg class="tree-decor__moon" viewBox="0 0 40 40"><circle cx="20" cy="20" r="14" fill="#FFF6D8"/><circle cx="26" cy="15" r="12" fill="#1B2448"/></svg>
            @endif
        </span>
    @endif

    @if ($on('snow'))
        <svg class="tree-decor__star" style="{{ $style($spots['star']) }}" viewBox="0 0 48 48">
            <defs><radialGradient id="tree-star-glow"><stop offset="0" stop-color="#FFE7A3" stop-opacity=".9"/><stop offset="1" stop-color="#FFE7A3" stop-opacity="0"/></radialGradient></defs>
            <circle cx="24" cy="24" r="24" fill="url(#tree-star-glow)"/>
            <path d="M24 7l4.7 10.6 11.5 1.1-8.7 7.7 2.5 11.3L24 31.8l-10 5.9 2.5-11.3-8.7-7.7 11.5-1.1z" fill="#F4C64E" stroke="#C9922A" stroke-width="1.2" stroke-linejoin="round"/>
        </svg>
        @foreach ($spots['ornaments'] as $i => $point)
            <span class="tree-ornament tree-ornament--{{ ['red', 'gold', 'green', 'blue'][$i % 4] }}" style="{{ $style($point) }} --sway: {{ 2.6 + ($i % 4) * 0.4 }}s; --delay: -{{ $i * 0.7 }}s;"></span>
        @endforeach
        <svg class="tree-decor__gift" style="{{ $style($spots['gifts'][0]) }}" viewBox="0 0 60 50">
            <rect x="4" y="16" width="52" height="34" rx="2" fill="#8B2A3D"/><rect x="2" y="10" width="56" height="10" rx="2" fill="#A3324A"/>
            <rect x="26" y="10" width="8" height="40" fill="#F2C38B"/><path d="M30 10c-6-9-16-6-12 0M30 10c6-9 16-6 12 0" fill="none" stroke="#F2C38B" stroke-width="3" stroke-linecap="round"/>
        </svg>
        <svg class="tree-decor__gift tree-decor__gift--small" style="{{ $style($spots['gifts'][1]) }}" viewBox="0 0 60 50">
            <rect x="6" y="18" width="48" height="32" rx="2" fill="#2F6B4F"/><rect x="4" y="12" width="52" height="9" rx="2" fill="#3C8562"/>
            <rect x="26" y="12" width="8" height="38" fill="#FFF8EF"/><path d="M30 12c-5-8-14-5-10 0M30 12c5-8 14-5 10 0" fill="none" stroke="#FFF8EF" stroke-width="3" stroke-linecap="round"/>
        </svg>
        <svg class="tree-decor__gift" style="{{ $style($spots['gifts'][2]) }}" viewBox="0 0 60 50">
            <rect x="4" y="16" width="52" height="34" rx="2" fill="#E9C27D"/><rect x="2" y="10" width="56" height="10" rx="2" fill="#F2D49A"/>
            <rect x="26" y="10" width="8" height="40" fill="#8B2A3D"/><path d="M30 10c-6-9-16-6-12 0M30 10c6-9 16-6 12 0" fill="none" stroke="#8B2A3D" stroke-width="3" stroke-linecap="round"/>
        </svg>
        <span class="tree-decor__floor tree-decor__floor--snow"></span>
    @endif

    @if ($on('autumn'))
        @foreach ($spots['leaves'] as $i => $leaf)
            <svg class="tree-leaf tree-leaf--{{ $tones[$i % 3] }} tree-leaf--fallen" style="{{ $style($leaf['at']) }} --turn: {{ $leaf['turn'] }}deg;" viewBox="0 0 24 24"><path d="M12 2C6.5 6 4.5 12 6.5 17.5 8.5 21 12 22 12 22s3.5-1 5.5-4.5C19.5 12 17.5 6 12 2z" fill="currentColor"/><path d="M12 5v17M12 11l-3-2M12 15l3-2" stroke="rgb(0 0 0 / 22%)" stroke-width="1" fill="none"/></svg>
        @endforeach
        @unless ($on('snow'))
            <svg class="tree-decor__pumpkin" style="{{ $style($spots['pumpkins'][0]) }}" viewBox="0 0 64 52">
                <path d="M32 10c-2-6 2-9 6-8" fill="none" stroke="#5B6B2F" stroke-width="4" stroke-linecap="round"/>
                <ellipse cx="20" cy="32" rx="14" ry="18" fill="#D9771F"/><ellipse cx="44" cy="32" rx="14" ry="18" fill="#D9771F"/>
                <ellipse cx="32" cy="32" rx="14" ry="19" fill="#EE8E2E"/>
                <path d="M32 14v36M22 15c-3 10-3 24 0 34M42 15c3 10 3 24 0 34" stroke="#B85F14" stroke-width="1.6" fill="none" opacity=".7"/>
            </svg>
            <svg class="tree-decor__pumpkin tree-decor__pumpkin--small" style="{{ $style($spots['pumpkins'][1]) }}" viewBox="0 0 64 52">
                <path d="M32 10c-2-6 2-9 6-8" fill="none" stroke="#5B6B2F" stroke-width="4" stroke-linecap="round"/>
                <ellipse cx="22" cy="33" rx="13" ry="16" fill="#E2A23A"/><ellipse cx="42" cy="33" rx="13" ry="16" fill="#E2A23A"/><ellipse cx="32" cy="33" rx="13" ry="17" fill="#F0B84C"/>
            </svg>
        @endunless
    @endif

    @if ($on('lanterns'))
        @foreach ($spots['lanterns'] as $i => $point)
            <span class="tree-lantern" style="{{ $style($point) }} --delay: -{{ $i * 1.3 }}s;"></span>
        @endforeach
    @endif

    @if ($on('nest'))
        {{-- robin in its nest, on a branch --}}
        <svg class="tree-creature tree-creature--nest" style="{{ $style($spots['nest']) }}" viewBox="0 0 90 70">
            <g class="tree-nest__bird">
                <ellipse cx="46" cy="30" rx="17" ry="14" fill="#7A5236"/>
                <path d="M58 26c8-2 14 1 18 4-6 1-12 2-17 1z" fill="#5E3E28"/>
                <circle cx="36" cy="20" r="10" fill="#7A5236"/>
                <path d="M30 26c4 9 14 11 20 8-4-5-12-8-20-8z" fill="#E2683C"/>
                <circle cx="33" cy="18" r="2" fill="#1d1410"/><circle cx="33.6" cy="17.4" r=".6" fill="#fff"/>
                <path d="M26 20l-7 2 7 2z" fill="#E8B23C"/>
            </g>
            <path d="M8 42c8 18 66 18 74 0-6 6-62 6-74 0z" fill="#8A6038"/>
            <path d="M10 40c10 10 60 10 70 0M12 46c10 9 56 9 66 0M18 52c12 6 42 6 54 0" stroke="#5B3A20" stroke-width="2" fill="none" stroke-linecap="round"/>
            <path d="M14 38l6 6M24 36l2 8M66 36l-3 8M76 38l-6 6M40 37l1 8M52 37l-1 8" stroke="#A47A4E" stroke-width="1.6" stroke-linecap="round"/>
        </svg>
    @endif

    @if ($on('swing'))
        {{-- rope swing hanging from a low branch --}}
        <svg class="tree-creature tree-creature--swing" style="{{ $style($spots['swing']) }}" viewBox="0 0 100 200">
            <g class="tree-swing">
                <path d="M18 0v168M82 0v168" stroke="#C9A36A" stroke-width="2.6"/>
                <path d="M18 0v168M82 0v168" stroke="#8A6A3A" stroke-width="2.6" stroke-dasharray="3 5" opacity=".6"/>
                <rect x="6" y="164" width="88" height="12" rx="3" fill="#8A5A36"/>
                <rect x="6" y="164" width="88" height="4" rx="2" fill="#A97A4E"/>
            </g>
        </svg>
    @endif

    @if ($on('squirrel'))
        {{-- squirrel climbing the trunk --}}
        <svg class="tree-creature tree-creature--squirrel" style="{{ $style($spots['squirrel']) }}" viewBox="0 0 80 110">
            <path class="tree-squirrel__tail" d="M44 60c26 2 34-30 18-46-8-8-22-4-20 8 2 10 14 8 12 18-2 8-14 10-14 20z" fill="#B5652B"/>
            <path d="M48 26c10-2 16 4 14 12" stroke="#D88A49" stroke-width="3" fill="none" stroke-linecap="round" opacity=".7"/>
            <ellipse cx="30" cy="66" rx="14" ry="22" fill="#A85A24"/>
            <ellipse cx="25" cy="70" rx="7" ry="14" fill="#E9C9A0"/>
            <circle cx="28" cy="40" r="11" fill="#A85A24"/>
            <path d="M22 31l-3-9 7 5zM33 29l1-9 5 7z" fill="#A85A24"/>
            <circle cx="24" cy="39" r="2.2" fill="#1d1410"/><circle cx="24.7" cy="38.3" r=".7" fill="#fff"/>
            <circle cx="18.5" cy="43" r="1.6" fill="#3a2214"/>
            <path d="M20 58l-8 6M22 80l-8 8M38 84l4 10" stroke="#8A4718" stroke-width="4" stroke-linecap="round"/>
        </svg>
    @endif

    @if ($on('owl'))
        {{-- owl perched on a branch; it blinks --}}
        <svg class="tree-creature tree-creature--owl" style="{{ $style($spots['owl']) }}" viewBox="0 0 70 90">
            <path d="M14 22l4-14 10 10M56 22l-4-14-10 10" fill="#6B4A32"/>
            <ellipse cx="35" cy="50" rx="25" ry="32" fill="#7D573A"/>
            <ellipse cx="35" cy="60" rx="16" ry="20" fill="#D9B98C"/>
            <path d="M24 54q3 3 6 0M33 58q3 3 6 0M42 54q3 3 6 0M28 66q3 3 6 0M37 66q3 3 6 0" stroke="#A88257" stroke-width="1.4" fill="none"/>
            <circle cx="24" cy="34" r="10" fill="#F3E3C3"/><circle cx="46" cy="34" r="10" fill="#F3E3C3"/>
            <g class="tree-owl__eyes"><circle cx="24" cy="34" r="5" fill="#2A1A10"/><circle cx="46" cy="34" r="5" fill="#2A1A10"/><circle cx="25.6" cy="32.4" r="1.6" fill="#fff"/><circle cx="47.6" cy="32.4" r="1.6" fill="#fff"/></g>
            <path d="M35 38l-4 6h8z" fill="#E1A23A"/>
            <path d="M22 80l-4 6M28 81l0 7M42 81l0 7M48 80l4 6" stroke="#E1A23A" stroke-width="2.4" stroke-linecap="round"/>
        </svg>
    @endif
</div>

{{-- Moving things on top: snowfall, drifting leaves, fireflies, butterflies --}}
<div class="tree-fx" aria-hidden="true">
    @if ($on('snow') && ! $windowSnow)
        <span class="tree-fx__snow"></span>
        <span class="tree-fx__snow tree-fx__snow--far"></span>
    @endif
    @if ($on('autumn'))
        @foreach (range(0, 9) as $i)
            <span class="tree-fall" style="left: {{ round(24 + $spread($i, 1) * 56, 2) }}%; --dur: {{ round(11 + $spread($i, 2) * 8, 1) }}s; --delay: -{{ round($spread($i, 3) * 18, 1) }}s; --drift: {{ round(-40 + $spread($i, 4) * 80) }}px;">
                <svg class="tree-leaf tree-leaf--{{ ['orange', 'red', 'gold'][$i % 3] }}" viewBox="0 0 24 24"><path d="M12 2C6.5 6 4.5 12 6.5 17.5 8.5 21 12 22 12 22s3.5-1 5.5-4.5C19.5 12 17.5 6 12 2z" fill="currentColor"/><path d="M12 5v17" stroke="rgb(0 0 0 / 22%)" stroke-width="1"/></svg>
            </span>
        @endforeach
    @endif
    @if ($on('evening'))
        @foreach (range(0, 15) as $i)
            <span class="tree-firefly" style="left: {{ round(22 + $spread($i, 5) * 56, 2) }}%; top: {{ round(6 + $spread($i, 6) * 70, 2) }}%; --dur: {{ round(5 + $spread($i, 7) * 6, 1) }}s; --delay: -{{ round($spread($i, 8) * 9, 1) }}s;"></span>
        @endforeach
    @endif
    @if ($on('butterflies'))
        @foreach ($spots['butterflies'] as $i => $point)
            <span class="tree-butterfly" style="{{ $style($point) }} --hue: {{ [0, 200, 40, 290][$i % 4] }}deg; --dur: {{ 9 + $i * 2 }}s; --delay: -{{ $i * 2.3 }}s;">
                <svg viewBox="0 0 40 30"><g class="tree-butterfly__wings"><path d="M20 15C14 2 2 2 3 10s9 9 17 5z" fill="#F28C38"/><path d="M20 15C14 22 5 27 7 21s7-7 13-6z" fill="#E36A2E"/><path d="M20 15C26 2 38 2 37 10s-9 9-17 5z" fill="#F28C38"/><path d="M20 15C26 22 35 27 33 21s-7-7-13-6z" fill="#E36A2E"/><circle cx="9" cy="9" r="1.6" fill="#fff"/><circle cx="31" cy="9" r="1.6" fill="#fff"/></g><path d="M20 8v15" stroke="#2A1A10" stroke-width="2" stroke-linecap="round"/></svg>
            </span>
        @endforeach
    @endif
</div>
@endif
