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
            'plant' => $at(Tree::OBJECTS['plant']),
            'candle' => $at(Tree::OBJECTS['candle']),
            'bookends' => $at(Tree::OBJECTS['bookends']),
            'cat' => $at(Tree::OBJECTS['cat']),
            'butterflies' => array_map($at, Tree::BUTTERFLIES),
            'window' => 'left: ' . $winTop['left'] . '%; top: ' . $winTop['top'] . '%; width: ' . round(($wx2 - $wx1) / Tree::WIDTH * 100, 3) . '%; height: ' . round($winBottom['top'] - $winTop['top'], 3) . '%;',
        ];
    }
    // Where each piece goes: its default spot, or where the reader moved it ($positions: key => [left, top]).
    $positions = $positions ?? [];
    $style = function (array $p, ?string $key = null) use ($positions) {
        if ($key !== null && isset($positions[$key][0], $positions[$key][1])) {
            $p = ['left' => (float) $positions[$key][0], 'top' => (float) $positions[$key][1]];
        }

        return 'left: ' . $p['left'] . '%; top: ' . $p['top'] . '%;';
    };
    $piece = fn (string $key) => ' data-decor-piece="' . $key . '"';
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
        @include('decor.parts.star', ['attrs' => 'class="tree-decor__star" style="' . $style($spots['star'], 'star') . '"' . $piece('star')])

        {{-- glass baubles --}}
        @foreach ($spots['ornaments'] as $i => $point)
            @include('decor.parts.bauble', ['attrs' => 'class="tree-ornament" style="' . $style($point, 'ornament-' . $i) . ' --sway: ' . (2.6 + ($i % 4) * 0.4) . 's; --delay: -' . ($i * 0.7) . 's;"' . $piece('ornament-' . $i), 'tone' => ['red', 'gold', 'green', 'blue'][$i % 4], 'pattern' => $i])
        @endforeach

        {{-- presents --}}
        @foreach ([['wine', ''], ['forest', 'tree-decor__gift--small'], ['cream', '']] as $g => [$tone, $size])
            @include('decor.parts.gift', ['attrs' => 'class="tree-decor__gift ' . $size . '" style="' . $style($spots['gifts'][$g], 'gift-' . $g) . '"' . $piece('gift-' . $g), 'tone' => $tone])
        @endforeach
    @endif

    @if ($on('autumn'))
        @foreach ($spots['leaves'] as $i => $leaf)
            <svg class="tree-leaf tree-leaf--{{ $tones[$i % 3] }} tree-leaf--fallen" style="{{ $style($leaf['at'], 'leaf-' . $i) }} --turn: {{ $leaf['turn'] }}deg;" data-decor-piece="leaf-{{ $i }}" viewBox="0 0 24 24"><path d="{{ $maple }}" fill="currentColor" stroke="#3E1A1C" stroke-width=".9" stroke-linejoin="round"/><path d="M12 21v-9M12 12l-4-4M12 12l4-4M12 14l-5 1M12 14l5 1" stroke="rgb(0 0 0 / 25%)" stroke-width=".8" fill="none"/></svg>
        @endforeach
        @unless ($on('snow'))
            @foreach ([0 => '', 1 => 'tree-decor__pumpkin--small'] as $p => $size)
                @include('decor.parts.pumpkin', ['attrs' => 'class="tree-decor__pumpkin ' . $size . '" style="' . $style($spots['pumpkins'][$p], 'pumpkin-' . $p) . '"' . $piece('pumpkin-' . $p), 'tone' => $p ? 'gold' : 'orange'])
            @endforeach
        @endunless
    @endif

    @if ($on('lanterns'))
        @foreach ($spots['lanterns'] as $i => $point)
            @include('decor.parts.lantern', ['attrs' => 'class="tree-lantern" style="' . $style($point, 'lantern-' . $i) . ' --delay: -' . ($i * 1.3) . 's;"' . $piece('lantern-' . $i)])
        @endforeach
    @endif

    @if ($on('nest'))
        {{-- a robin in its nest, with an egg --}}
        @include('decor.parts.nest', ['attrs' => 'class="tree-creature tree-creature--nest" style="' . $style($spots['nest'], 'nest') . '"' . $piece('nest')])
    @endif

    @if ($on('swing'))
        {{-- a wooden swing on twisted ropes, with a few flowers --}}
        @include('decor.parts.swing', ['attrs' => 'class="tree-creature tree-creature--swing" style="' . $style($spots['swing'], 'swing') . '"' . $piece('swing')])
    @endif

    @if ($on('squirrel'))
        {{-- a red squirrel holding an acorn --}}
        @include('decor.parts.squirrel', ['attrs' => 'class="tree-creature tree-creature--squirrel" style="' . $style($spots['squirrel'], 'squirrel') . '"' . $piece('squirrel')])
    @endif

    @foreach (['plant', 'candle', 'bookends', 'cat'] as $object)
        @if ($on($object) && isset($spots[$object]))
            @include('decor.parts.' . $object, ['attrs' => 'class="tree-object tree-object--' . $object . '" style="' . $style($spots[$object], $object) . '"' . $piece($object)])
        @endif
    @endforeach

    @if ($on('owl'))
        {{-- a brown owl; it blinks --}}
        @include('decor.parts.owl', ['attrs' => 'class="tree-creature tree-creature--owl" style="' . $style($spots['owl'], 'owl') . '"' . $piece('owl')])
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
                <svg class="tree-leaf tree-leaf--{{ $tones[$i % 3] }}" viewBox="0 0 24 24"><path d="{{ $maple }}" fill="currentColor" stroke="#3E1A1C" stroke-width=".9" stroke-linejoin="round"/><path d="M12 21v-9" stroke="rgb(0 0 0 / 25%)" stroke-width=".8"/></svg>
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
            <span class="tree-butterfly" data-decor-piece="butterfly-{{ $i }}" style="{{ $style($point, 'butterfly-' . $i) }} --hue: {{ [0, 190, 300, 40][$i % 4] }}deg; --dur: {{ 9 + $i * 2 }}s; --delay: -{{ $i * 2.3 }}s;">
                @include('decor.parts.butterfly')
            </span>
        @endforeach
    @endif
</div>
@endif
