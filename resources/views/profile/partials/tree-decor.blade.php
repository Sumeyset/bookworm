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

@php
    $maple = 'M12 1.5l1.6 4 3-1.6-.9 3.8 3.9-.6-2.2 3.2 3.1 1.6-3.7 1.3.9 2.1-3.3-.6.2 4.1-2.6-1.9-2.6 1.9.2-4.1-3.3.6.9-2.1L2.5 11.5l3.1-1.6-2.2-3.2 3.9.6-.9-3.8 3 1.6z';
@endphp

@if ($decorations)
{{-- Shared paints for the decorations --}}
<svg width="0" height="0" class="absolute" aria-hidden="true" focusable="false">
    <defs>
        <radialGradient id="dec-glow" cx=".5" cy=".5" r=".5"><stop offset="0" stop-color="#FFE7A3" stop-opacity=".95"/><stop offset=".45" stop-color="#FFD36B" stop-opacity=".35"/><stop offset="1" stop-color="#FFD36B" stop-opacity="0"/></radialGradient>
        <linearGradient id="dec-gold" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#FFF1B8"/><stop offset=".45" stop-color="#F4C64E"/><stop offset="1" stop-color="#B9852A"/></linearGradient>
        <linearGradient id="dec-gold-dark" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#F2C24A"/><stop offset="1" stop-color="#9C6A1C"/></linearGradient>
        @foreach (['red' => ['#FF8A8F', '#C8303F', '#6E0F1C'], 'gold' => ['#FFF0B0', '#D9A53A', '#7A5212'], 'green' => ['#8FE0B4', '#2F7D57', '#0F3A27'], 'blue' => ['#A9CBFF', '#3F6FB5', '#14305E']] as $name => [$light, $mid, $dark])
            <radialGradient id="dec-ball-{{ $name }}" cx=".36" cy=".32" r=".75"><stop offset="0" stop-color="{{ $light }}"/><stop offset=".45" stop-color="{{ $mid }}"/><stop offset="1" stop-color="{{ $dark }}"/></radialGradient>
        @endforeach
        @foreach (['wine' => ['#B8435A', '#7A2133'], 'forest' => ['#4E9A73', '#235740'], 'cream' => ['#FFF0CF', '#E2BF7E']] as $name => [$top, $bottom])
            <linearGradient id="dec-box-{{ $name }}" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="{{ $top }}"/><stop offset="1" stop-color="{{ $bottom }}"/></linearGradient>
        @endforeach
        <linearGradient id="dec-lantern-glass" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#FFF6D2"/><stop offset=".55" stop-color="#FFC861"/><stop offset="1" stop-color="#E58A2B"/></linearGradient>
        <linearGradient id="dec-bronze" x1="0" y1="0" x2="1" y2="0"><stop offset="0" stop-color="#3B2414"/><stop offset=".5" stop-color="#8A5A2E"/><stop offset="1" stop-color="#3B2414"/></linearGradient>
        <radialGradient id="dec-pumpkin" cx=".4" cy=".35" r=".8"><stop offset="0" stop-color="#FFB45C"/><stop offset=".6" stop-color="#E9792A"/><stop offset="1" stop-color="#A9480F"/></radialGradient>
        <radialGradient id="dec-owl-body" cx=".5" cy=".3" r=".8"><stop offset="0" stop-color="#A47A54"/><stop offset="1" stop-color="#5A3B24"/></radialGradient>
        <linearGradient id="dec-wing" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#FFB347"/><stop offset=".6" stop-color="#F07F1C"/><stop offset="1" stop-color="#C4520C"/></linearGradient>
        <linearGradient id="dec-tail" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#D9864A"/><stop offset="1" stop-color="#8E4A1D"/></linearGradient>
        <linearGradient id="dec-wood" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#C58E5A"/><stop offset="1" stop-color="#7C4F2C"/></linearGradient>
    </defs>
</svg>

<div class="tree-decor" aria-hidden="true">
    @if ($windowTheme)
        <span class="tree-decor__window tree-decor__window--{{ $windowTheme }}" style="{{ $spots['window'] }}">
            @if ($windowTheme === 'night')
                <svg class="tree-decor__moon" viewBox="0 0 40 40"><circle cx="20" cy="20" r="14" fill="#FFF6D8"/><circle cx="26" cy="15" r="12" fill="#1B2448"/></svg>
            @endif
        </span>
    @endif

    @if ($on('snow'))
        <span class="tree-decor__floor tree-decor__floor--snow"></span>
        {{-- a glowing star --}}
        <svg class="tree-decor__star" style="{{ $style($spots['star']) }}" viewBox="0 0 64 64">
            <circle cx="32" cy="32" r="31" fill="url(#dec-glow)"/>
            <path d="M32 9l6.5 14.4 15.7 1.6-11.8 10.5 3.4 15.4L32 43.1l-13.8 7.8 3.4-15.4L9.8 25l15.7-1.6z" fill="url(#dec-gold)" stroke="#9C6A1C" stroke-width="1.2" stroke-linejoin="round"/>
            <path d="M32 9l6.5 14.4L32 32zM54.2 25L42.4 35.5 32 32zM45.8 50.9L32 43.1V32zM18.2 50.9l3.4-15.4L32 32zM9.8 25l15.7-1.6L32 32z" fill="#FFF6D2" opacity=".35"/>
            <g fill="#FFF8E0"><circle cx="50" cy="12" r="1.4"/><circle cx="13" cy="15" r="1"/><circle cx="55" cy="40" r="1"/></g>
        </svg>

        {{-- glass baubles --}}
        @foreach ($spots['ornaments'] as $i => $point)
            @php $tone = ['red', 'gold', 'green', 'blue'][$i % 4]; @endphp
            <svg class="tree-ornament" style="{{ $style($point) }} --sway: {{ 2.6 + ($i % 4) * 0.4 }}s; --delay: -{{ $i * 0.7 }}s;" viewBox="0 0 40 64">
                <path d="M20 0v14" stroke="#B89A6A" stroke-width="1"/>
                <circle cx="20" cy="15" r="2.6" fill="none" stroke="url(#dec-gold)" stroke-width="1.4"/>
                <rect x="14.5" y="17" width="11" height="7" rx="1.6" fill="url(#dec-gold)"/>
                <path d="M15 20h10" stroke="#9C6A1C" stroke-width=".7"/>
                <circle cx="20" cy="41" r="17" fill="url(#dec-ball-{{ $tone }})"/>
                @if ($i % 2)
                    <path d="M3.6 37c10 4 22.8 4 32.8 0M3.4 44c10 4 23.2 4 33.2 0" stroke="#FFE7A3" stroke-width="1.4" fill="none" opacity=".85"/>
                @else
                    <g fill="#FFF6E0" opacity=".9"><circle cx="13" cy="44" r="1.4"/><circle cx="20" cy="47" r="1.4"/><circle cx="27" cy="44" r="1.4"/><path d="M20 33l1.2 2.6 2.8.3-2.1 1.9.6 2.8-2.5-1.4-2.5 1.4.6-2.8-2.1-1.9 2.8-.3z"/></g>
                @endif
                <ellipse cx="13" cy="33" rx="4.5" ry="3" fill="#fff" opacity=".55" transform="rotate(-30 13 33)"/>
            </svg>
        @endforeach

        {{-- presents --}}
        @foreach ([['wine', '#F2C38B', ''], ['forest', '#FFF8EF', 'tree-decor__gift--small'], ['cream', '#A3324A', '']] as $g => [$box, $ribbon, $size])
            <svg class="tree-decor__gift {{ $size }}" style="{{ $style($spots['gifts'][$g]) }}" viewBox="0 0 64 60">
                <ellipse cx="32" cy="58" rx="28" ry="2.5" fill="#000" opacity=".18"/>
                <rect x="6" y="24" width="52" height="34" rx="2.5" fill="url(#dec-box-{{ $box }})"/>
                <rect x="6" y="24" width="52" height="34" rx="2.5" fill="none" stroke="#000" stroke-opacity=".12"/>
                <rect x="3" y="17" width="58" height="10" rx="2.5" fill="url(#dec-box-{{ $box }})"/>
                <rect x="3" y="25" width="58" height="2" fill="#000" opacity=".12"/>
                <rect x="28" y="17" width="8" height="41" fill="{{ $ribbon }}"/>
                <rect x="28" y="17" width="2.5" height="41" fill="#fff" opacity=".35"/>
                <path d="M32 17c-4-11-17-11-15-3 1 4 9 4 15 3zM32 17c4-11 17-11 15-3-1 4-9 4-15 3z" fill="{{ $ribbon }}" stroke="#000" stroke-opacity=".15"/>
                <path d="M32 17c-6 2-9 7-11 11M32 17c6 2 9 7 11 11" stroke="{{ $ribbon }}" stroke-width="3" stroke-linecap="round" fill="none"/>
                <circle cx="32" cy="17" r="3.2" fill="{{ $ribbon }}" stroke="#000" stroke-opacity=".15"/>
            </svg>
        @endforeach
    @endif

    @if ($on('autumn'))
        @foreach ($spots['leaves'] as $i => $leaf)
            <svg class="tree-leaf tree-leaf--{{ $tones[$i % 3] }} tree-leaf--fallen" style="{{ $style($leaf['at']) }} --turn: {{ $leaf['turn'] }}deg;" viewBox="0 0 24 24"><path d="{{ $maple }}" fill="currentColor"/><path d="M12 21v-9M12 12l-4-4M12 12l4-4M12 14l-5 1M12 14l5 1" stroke="rgb(0 0 0 / 25%)" stroke-width=".8" fill="none"/></svg>
        @endforeach
        @unless ($on('snow'))
            @foreach ([0 => '', 1 => 'tree-decor__pumpkin--small'] as $p => $size)
                <svg class="tree-decor__pumpkin {{ $size }}" style="{{ $style($spots['pumpkins'][$p]) }}" viewBox="0 0 72 60">
                    <ellipse cx="36" cy="58" rx="30" ry="2.5" fill="#000" opacity=".18"/>
                    <path d="M36 14c-1-6 1-10 6-11" fill="none" stroke="#5B6B2F" stroke-width="4.5" stroke-linecap="round"/>
                    <path d="M42 8c6-4 13-1 13 5-5 1-10 0-13-5z" fill="#6E8B3D"/>
                    <path d="M44 10c4 1 6 3 9 3" stroke="#4E6A28" stroke-width="1" fill="none"/>
                    <ellipse cx="18" cy="36" rx="14" ry="20" fill="url(#dec-pumpkin)"/>
                    <ellipse cx="54" cy="36" rx="14" ry="20" fill="url(#dec-pumpkin)"/>
                    <ellipse cx="27" cy="36" rx="13" ry="21" fill="url(#dec-pumpkin)"/>
                    <ellipse cx="45" cy="36" rx="13" ry="21" fill="url(#dec-pumpkin)"/>
                    <ellipse cx="36" cy="36" rx="12" ry="22" fill="url(#dec-pumpkin)"/>
                    <path d="M27 16c-3 12-3 28 0 40M45 16c3 12 3 28 0 40M36 14v44" stroke="#A9480F" stroke-width="1.2" fill="none" opacity=".55"/>
                    <ellipse cx="30" cy="26" rx="4" ry="7" fill="#fff" opacity=".18"/>
                </svg>
            @endforeach
        @endunless
    @endif

    @if ($on('lanterns'))
        @foreach ($spots['lanterns'] as $i => $point)
            <svg class="tree-lantern" style="{{ $style($point) }} --delay: -{{ $i * 1.3 }}s;" viewBox="0 0 40 74">
                <path d="M20 0v8" stroke="#6B4A2A" stroke-width="1.2"/>
                <circle cx="20" cy="10" r="3" fill="none" stroke="url(#dec-bronze)" stroke-width="2"/>
                <ellipse cx="20" cy="45" rx="19" ry="22" fill="url(#dec-glow)"/>
                <path d="M9 20h22l-3-6H12z" fill="url(#dec-bronze)"/>
                <rect x="8" y="20" width="24" height="3" rx="1" fill="#5A3A1E"/>
                <path d="M10 23h20l-2 34H12z" fill="url(#dec-lantern-glass)" opacity=".95"/>
                <path d="M15.5 23l-.7 34M24.5 23l.7 34" stroke="#5A3A1E" stroke-width="1.6"/>
                <path d="M20 36c3 4 3 9 0 12-3-3-3-8 0-12z" fill="#FFF7D6"/>
                <path d="M20 41c1.4 2 1.4 4.6 0 6-1.4-1.4-1.4-4 0-6z" fill="#FF9A3C"/>
                <rect x="9" y="57" width="22" height="4" rx="1.5" fill="url(#dec-bronze)"/>
                <path d="M13 61h14l-2 4H15z" fill="#4A2E18"/>
                <path d="M11.5 25l1 30" stroke="#fff" stroke-width="1.4" opacity=".45"/>
            </svg>
        @endforeach
    @endif

    @if ($on('nest'))
        {{-- a robin in its nest, with eggs --}}
        <svg class="tree-creature tree-creature--nest" style="{{ $style($spots['nest']) }}" viewBox="0 0 100 78">
            <ellipse cx="50" cy="74" rx="40" ry="3.5" fill="#000" opacity=".2"/>
            <g class="tree-nest__bird">
                <path d="M66 36c10-3 18-1 24 4-8 2-16 2-23 0z" fill="#4A3020"/>
                <ellipse cx="54" cy="38" rx="19" ry="15" fill="#6B4A32"/>
                <path d="M46 30c-6 6-5 15 2 19 4-6 4-13-2-19z" fill="#5A3B26"/>
                <circle cx="40" cy="26" r="11" fill="#6B4A32"/>
                <path d="M31 30c3 11 16 15 25 9-5-7-15-10-25-9z" fill="#E86A33"/>
                <path d="M33 32c4 6 11 8 17 6" stroke="#FF9A5C" stroke-width="1.4" fill="none" opacity=".7"/>
                <circle cx="37" cy="23" r="2.4" fill="#1D1410"/><circle cx="37.8" cy="22.2" r=".8" fill="#fff"/>
                <path d="M30 25l-8 2.4 8 2z" fill="#E8B23C"/>
                <path d="M58 30c5 1 9 4 11 8" stroke="#8C6446" stroke-width="1.6" fill="none"/>
            </g>
            <ellipse cx="32" cy="44" rx="5" ry="6" fill="#9FD3E6"/><ellipse cx="30.8" cy="42.2" rx="1.6" ry="2" fill="#fff" opacity=".6"/>
            <path d="M8 46c6 22 78 22 84 0-8 7-76 7-84 0z" fill="#8A6038"/>
            <path d="M8 46c8-3 76-3 84 0" stroke="#A47A4E" stroke-width="3" fill="none"/>
            <path d="M10 48c12 10 68 10 80 0M14 54c12 8 60 8 72 0M20 60c12 6 48 6 60 0" stroke="#5B3A20" stroke-width="2" fill="none" stroke-linecap="round"/>
            <path d="M12 44l8 7M26 42l2 9M74 42l-3 9M88 44l-8 7M46 43l1 9M58 43l-1 9M5 47l7-2M95 47l-7-2" stroke="#B88A58" stroke-width="1.8" stroke-linecap="round"/>
        </svg>
    @endif

    @if ($on('swing'))
        {{-- a wooden swing on twisted ropes, with a few flowers --}}
        <svg class="tree-creature tree-creature--swing" style="{{ $style($spots['swing']) }}" viewBox="0 0 100 200">
            <g class="tree-swing">
                <path d="M18 0v168M82 0v168" stroke="#C9A36A" stroke-width="3.2"/>
                <path d="M18 0v168M82 0v168" stroke="#8A6A3A" stroke-width="3.2" stroke-dasharray="2.5 3.5" opacity=".7"/>
                <g fill="#F7B7C6"><circle cx="15" cy="60" r="3"/><circle cx="21" cy="64" r="3"/><circle cx="16" cy="66" r="3"/></g><circle cx="17.5" cy="63.5" r="1.6" fill="#F4C64E"/>
                <g fill="#FFF0F4"><circle cx="79" cy="96" r="3"/><circle cx="85" cy="100" r="3"/><circle cx="80" cy="102" r="3"/></g><circle cx="81.5" cy="99.5" r="1.6" fill="#F4C64E"/>
                <path d="M21 62c3-3 6-2 8 1M77 98c-3-3-6-2-8 1" stroke="#6E8B3D" stroke-width="1.6" fill="none"/>
                <rect x="4" y="164" width="92" height="14" rx="3.5" fill="url(#dec-wood)"/>
                <path d="M10 169h34M50 173h38" stroke="#5A3A1E" stroke-width="1" opacity=".5"/>
                <rect x="4" y="164" width="92" height="3.5" rx="1.75" fill="#E2B27C" opacity=".8"/>
                <circle cx="18" cy="166" r="3.4" fill="#8A6A3A"/><circle cx="82" cy="166" r="3.4" fill="#8A6A3A"/>
            </g>
        </svg>
    @endif

    @if ($on('squirrel'))
        {{-- a red squirrel holding an acorn --}}
        <svg class="tree-creature tree-creature--squirrel" style="{{ $style($spots['squirrel']) }}" viewBox="0 0 90 110">
            <path class="tree-squirrel__tail" d="M50 74c30 2 40-34 22-56C62 6 44 10 46 24c2 11 16 9 14 21-2 9-16 13-10 29z" fill="url(#dec-tail)"/>
            <path class="tree-squirrel__tail" d="M58 22c10-1 16 7 14 17M62 32c6 1 8 6 7 11" stroke="#F0A86A" stroke-width="2.4" fill="none" stroke-linecap="round" opacity=".7"/>
            <ellipse cx="34" cy="70" rx="16" ry="24" fill="#B4602A"/>
            <ellipse cx="29" cy="74" rx="8" ry="15" fill="#F2D6B0"/>
            <circle cx="32" cy="40" r="13" fill="#B4602A"/>
            <path d="M24 30l-3-11 8 6zM37 28l3-11 4 9z" fill="#B4602A"/>
            <path d="M24.5 27l-1.4-5 3.2 2.6zM38.5 25.5l1.6-5 1.6 4.2z" fill="#F2A46E"/>
            <ellipse cx="24" cy="44" rx="7" ry="6" fill="#F2D6B0"/>
            <circle cx="27" cy="38" r="2.6" fill="#1D1410"/><circle cx="27.8" cy="37.2" r=".9" fill="#fff"/>
            <circle cx="18.5" cy="44" r="1.8" fill="#3A2214"/>
            <path d="M19 47c2 1.5 4 1.5 6 0" stroke="#3A2214" stroke-width="1" fill="none"/>
            <ellipse cx="22" cy="60" rx="6" ry="7" fill="#8E5A2B"/>
            <path d="M16.5 56.5c2-4 9-4 11 0z" fill="#5A3A1E"/>
            <path d="M22 52.5v-2.5" stroke="#5A3A1E" stroke-width="1.4" stroke-linecap="round"/>
            <path d="M26 58c-3 3-7 3-9 0M28 64c-3 3-8 3-10 0" stroke="#9A4F20" stroke-width="3.4" stroke-linecap="round" fill="none"/>
            <path d="M24 90l-8 8M38 92l3 10" stroke="#9A4F20" stroke-width="4.5" stroke-linecap="round"/>
        </svg>
    @endif

    @if ($on('owl'))
        {{-- a barn-brown owl; it blinks --}}
        <svg class="tree-creature tree-creature--owl" style="{{ $style($spots['owl']) }}" viewBox="0 0 80 100">
            <ellipse cx="40" cy="97" rx="26" ry="3" fill="#000" opacity=".2"/>
            <path d="M16 26L19 6l13 12M64 26L61 6 48 18" fill="#5A3B24"/>
            <ellipse cx="40" cy="56" rx="28" ry="36" fill="url(#dec-owl-body)"/>
            <path d="M14 50c-6 14-3 30 8 38 1-14-2-28-8-38zM66 50c6 14 3 30-8 38-1-14 2-28 8-38z" fill="#4A2F1C"/>
            <path d="M15 58c3 6 4 14 4 22M65 58c-3 6-4 14-4 22" stroke="#7A5638" stroke-width="1.4" fill="none"/>
            <ellipse cx="40" cy="66" rx="17" ry="22" fill="#E8D1A8"/>
            <path d="M30 58q3 3.4 6 0M39 62q3 3.4 6 0M44 56q3 3.4 6 0M33 68q3 3.4 6 0M42 72q3 3.4 6 0M34 78q3 3.4 6 0M43 82q3 3.4 6 0" stroke="#A88257" stroke-width="1.4" fill="none"/>
            <path d="M14 36c4-12 22-16 26-6 4-10 22-6 26 6-6 10-20 12-26 4-6 8-20 6-26-4z" fill="#F4E4C4"/>
            <g class="tree-owl__eyes">
                <circle cx="28" cy="34" r="9" fill="#F3B23A"/><circle cx="52" cy="34" r="9" fill="#F3B23A"/>
                <circle cx="28" cy="34" r="5.4" fill="#1D1410"/><circle cx="52" cy="34" r="5.4" fill="#1D1410"/>
                <circle cx="30" cy="31.8" r="2" fill="#fff"/><circle cx="54" cy="31.8" r="2" fill="#fff"/>
                <circle cx="26.4" cy="36.4" r=".9" fill="#fff" opacity=".7"/><circle cx="50.4" cy="36.4" r=".9" fill="#fff" opacity=".7"/>
            </g>
            <path d="M40 38l-4.5 6.5h9z" fill="#E1A23A"/><path d="M40 44.5l-2-3h4z" fill="#B87A1E"/>
            <path d="M28 92l-4 5M33 93v5M47 93v5M52 92l4 5" stroke="#E1A23A" stroke-width="2.6" stroke-linecap="round"/>
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
                <svg class="tree-leaf tree-leaf--{{ $tones[$i % 3] }}" viewBox="0 0 24 24"><path d="{{ $maple }}" fill="currentColor"/><path d="M12 21v-9" stroke="rgb(0 0 0 / 25%)" stroke-width=".8"/></svg>
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
            <span class="tree-butterfly" style="{{ $style($point) }} --hue: {{ [0, 190, 300, 40][$i % 4] }}deg; --dur: {{ 9 + $i * 2 }}s; --delay: -{{ $i * 2.3 }}s;">
                <svg viewBox="0 0 48 36">
                    <g class="tree-butterfly__wings">
                        <path d="M24 17C18 2 4 0 3 9s9 11 21 8z" fill="url(#dec-wing)" stroke="#2A1A10" stroke-width="1.2"/>
                        <path d="M24 19C17 22 8 31 11 33s11-5 13-14z" fill="url(#dec-wing)" stroke="#2A1A10" stroke-width="1.2"/>
                        <path d="M24 17c6-15 20-17 21-8s-9 11-21 8z" fill="url(#dec-wing)" stroke="#2A1A10" stroke-width="1.2"/>
                        <path d="M24 19c7 3 16 12 13 14s-11-5-13-14z" fill="url(#dec-wing)" stroke="#2A1A10" stroke-width="1.2"/>
                        <path d="M23 15C17 7 10 5 6 8M23 17C15 14 9 13 5 11M25 15c6-8 13-10 17-7M25 17c8-3 14-4 18-6M23 20c-4 4-8 8-10 11M25 20c4 4 8 8 10 11" stroke="#2A1A10" stroke-width=".9" fill="none" opacity=".75"/>
                        <g fill="#fff"><circle cx="5" cy="8" r="1"/><circle cx="8" cy="5" r=".8"/><circle cx="43" cy="8" r="1"/><circle cx="40" cy="5" r=".8"/><circle cx="12" cy="31" r=".8"/><circle cx="36" cy="31" r=".8"/></g>
                    </g>
                    <ellipse cx="24" cy="19" rx="1.8" ry="8" fill="#2A1A10"/>
                    <path d="M23 11c-2-4-4-6-6-7M25 11c2-4 4-6 6-7" stroke="#2A1A10" stroke-width=".9" fill="none"/>
                </svg>
            </span>
        @endforeach
    @endif
</div>
@endif
