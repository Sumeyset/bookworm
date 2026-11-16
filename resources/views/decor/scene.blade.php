{{--
    A little illustrated scene for a Tree shop item, built from the same
    drawings that go on the shelves (resources/views/decor/parts).
    Expects $item (an Acorns::ITEMS key).
--}}
@php
    $u = 'sc' . Str::random(5);
    $at = fn ($x, $y, $w, $h, $extra = '') => 'x="' . $x . '" y="' . $y . '" width="' . $w . '" height="' . $h . '" ' . $extra;
    $maple = 'M12 1.5l1.6 4 3-1.6-.9 3.8 3.9-.6-2.2 3.2 3.1 1.6-3.7 1.3.9 2.1-3.3-.6.2 4.1-2.6-1.9-2.6 1.9.2-4.1-3.3.6.9-2.1L2.5 11.5l3.1-1.6-2.2-3.2 3.9.6-.9-3.8 3 1.6z';
    $branch = '<path d="M-4 14c30 2 52 8 84 4s56-10 88-6" fill="none" stroke="#3E1A1C" stroke-width="7" stroke-linecap="round"/><path d="M-4 14c30 2 52 8 84 4s56-10 88-6" fill="none" stroke="#8A5A33" stroke-width="4.6" stroke-linecap="round"/><path d="M40 16c4-6 10-8 15-8M112 11c4 5 9 7 14 6" stroke="#8A5A33" stroke-width="2.4" fill="none" stroke-linecap="round"/><g stroke="#3E1A1C" stroke-width=".9"><ellipse cx="57" cy="8" rx="6" ry="3" fill="#7FA35B" transform="rotate(-20 57 8)"/><ellipse cx="128" cy="17" rx="6" ry="3" fill="#7FA35B" transform="rotate(25 128 17)"/><ellipse cx="22" cy="10" rx="5" ry="2.6" fill="#93B86A" transform="rotate(-12 22 10)"/></g>';
@endphp
<svg viewBox="0 0 160 100" xmlns="http://www.w3.org/2000/svg" class="decor-scene decor-scene--{{ $item }}" aria-hidden="true">
    <defs>
        <radialGradient id="{{ $u }}glow"><stop offset="0" stop-color="#FFE3A0" stop-opacity=".95"/><stop offset="1" stop-color="#FFE3A0" stop-opacity="0"/></radialGradient>
        <linearGradient id="{{ $u }}night" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#141A3A"/><stop offset="1" stop-color="#3B4C86"/></linearGradient>
    </defs>

    @switch($item)
        @case('lights')
            <path d="M6 30q37 30 74 0t74 0" fill="none" stroke="#3E1A1C" stroke-width="1.6"/>
            @foreach ([[17, 41, '#FFD36B'], [33, 47, '#FF6F7D'], [50, 43, '#80E0B0'], [67, 34, '#87B8FF'], [93, 34, '#FFD36B'], [110, 43, '#FF6F7D'], [127, 47, '#80E0B0'], [143, 41, '#87B8FF']] as $i => [$x, $y, $c])
                <circle cx="{{ $x }}" cy="{{ $y + 6 }}" r="11" fill="url(#{{ $u }}glow)" class="decor-scene__twinkle" style="animation-delay: -{{ $i * .3 }}s"/>
                <rect x="{{ $x - 2.4 }}" y="{{ $y - 3 }}" width="4.8" height="4" rx="1" fill="#6B5A48" stroke="#3E1A1C" stroke-width=".8"/>
                <ellipse cx="{{ $x }}" cy="{{ $y + 5 }}" rx="4.2" ry="5.6" fill="{{ $c }}" stroke="#3E1A1C" stroke-width="1.1"/>
                <ellipse cx="{{ $x - 1.3 }}" cy="{{ $y + 3.4 }}" rx="1.2" ry="2" fill="#fff" opacity=".7"/>
            @endforeach
            <rect x="8" y="78" width="144" height="10" rx="2.5" fill="#9C6B3F" stroke="#3E1A1C" stroke-width="1.4"/>
            <rect x="10" y="79" width="140" height="3" rx="1.5" fill="#C99566"/>
            @break

        @case('butterflies')
            {!! $branch !!}
            @include('decor.parts.butterfly', ['attrs' => $at(30, 34, 48, 36)])
            @include('decor.parts.butterfly', ['attrs' => $at(92, 52, 38, 28, 'style="filter: hue-rotate(190deg)"')])
            <path d="M80 50q8-12 18-6" fill="none" stroke="#B89A6A" stroke-width="1" stroke-dasharray="2 3"/>
            @break

        @case('lanterns')
            {!! $branch !!}
            @include('decor.parts.lantern', ['attrs' => $at(22, 16, 30, 56)])
            @include('decor.parts.lantern', ['attrs' => $at(65, 18, 34, 72)])
            @include('decor.parts.lantern', ['attrs' => $at(112, 12, 28, 52)])
            @break

        @case('nest')
            <path d="M-4 78c40-8 90-4 170-14" fill="none" stroke="#3E1A1C" stroke-width="9" stroke-linecap="round"/>
            <path d="M-4 78c40-8 90-4 170-14" fill="none" stroke="#8A5A33" stroke-width="6.4" stroke-linecap="round"/>
            <g stroke="#3E1A1C" stroke-width=".9"><ellipse cx="22" cy="68" rx="8" ry="4" fill="#7FA35B" transform="rotate(-25 22 68)"/><ellipse cx="140" cy="58" rx="8" ry="4" fill="#93B86A" transform="rotate(20 140 58)"/></g>
            @include('decor.parts.nest', ['attrs' => $at(38, 12, 86, 67)])
            @break

        @case('swing')
            {!! $branch !!}
            @include('decor.parts.swing', ['attrs' => $at(56, 14, 48, 96)])
            @break

        @case('squirrel')
            <path d="M0 88c50-6 110-6 160 0v12H0z" fill="#8FB070" stroke="#3E1A1C" stroke-width="1.2"/>
            @include('decor.parts.squirrel', ['attrs' => $at(52, 6, 66, 82)])
            @foreach ([[30, 82], [128, 84]] as [$x, $y])
                <ellipse cx="{{ $x }}" cy="{{ $y }}" rx="5" ry="6" fill="#9A642F" stroke="#3E1A1C" stroke-width="1"/>
                <path d="M{{ $x - 5.6 }} {{ $y - 3 }}c2-5 9.2-5 11.2 0z" fill="#5A3A1E" stroke="#3E1A1C" stroke-width="1"/>
            @endforeach
            @break

        @case('owl')
            <circle cx="124" cy="26" r="20" fill="url(#{{ $u }}glow)" opacity=".7"/>
            <path d="M120 10a16 16 0 1 0 16 25 13 13 0 1 1-16-25z" fill="#FFF3C8" stroke="#3E1A1C" stroke-width="1.2"/>
            <g fill="#E9C27D"><circle cx="146" cy="12" r="1.2"/><circle cx="100" cy="16" r="1"/><circle cx="150" cy="44" r=".9"/></g>
            <path d="M-4 88c50-6 110-4 170-10" fill="none" stroke="#3E1A1C" stroke-width="9" stroke-linecap="round"/>
            <path d="M-4 88c50-6 110-4 170-10" fill="none" stroke="#8A5A33" stroke-width="6.4" stroke-linecap="round"/>
            @include('decor.parts.owl', ['attrs' => $at(54, 8, 58, 76)])
            @break

        @case('evening')
            <rect x="0" y="0" width="160" height="100" fill="url(#{{ $u }}night)"/>
            <g fill="#fff">
                @foreach ([[14, 14], [36, 30], [58, 10], [82, 22], [104, 8], [146, 34], [24, 50], [70, 44], [132, 54]] as [$x, $y])
                    <circle cx="{{ $x }}" cy="{{ $y }}" r="{{ 0.6 + ($x % 3) * .3 }}" class="decor-scene__twinkle" style="animation-delay: -{{ $y / 10 }}s"/>
                @endforeach
            </g>
            <path d="M118 12a20 20 0 1 0 18 30 16 16 0 1 1-18-30z" fill="#FFF3C8" stroke="#3E1A1C" stroke-width="1.2"/>
            @foreach ([[40, 76], [62, 64], [90, 80], [110, 70], [26, 66]] as $i => [$x, $y])
                <circle cx="{{ $x }}" cy="{{ $y }}" r="8" fill="url(#{{ $u }}glow)" class="decor-scene__twinkle" style="animation-delay: -{{ $i * .5 }}s"/>
                <circle cx="{{ $x }}" cy="{{ $y }}" r="1.8" fill="#FFF7A8"/>
            @endforeach
            <path d="M0 92c40-8 120-8 160 0v8H0z" fill="#0E1228"/>
            @break

        @case('autumn')
            @include('decor.parts.pumpkin', ['attrs' => $at(22, 34, 72, 60)])
            @include('decor.parts.pumpkin', ['attrs' => $at(88, 54, 44, 37), 'tone' => 'gold'])
            @foreach ([[18, 16, -20, '#E07B2A'], [62, 8, 30, '#B8402B'], [118, 14, 70, '#E3A63B'], [138, 40, -40, '#E07B2A'], [12, 86, 10, '#B8402B'], [140, 86, 50, '#E3A63B']] as [$x, $y, $r, $c])
                <path d="{{ $maple }}" transform="translate({{ $x }} {{ $y }}) rotate({{ $r }} 12 12) scale(.8)" fill="{{ $c }}" stroke="#3E1A1C" stroke-width="1"/>
            @endforeach
            @break

        @case('snow')
            @include('decor.parts.star', ['attrs' => $at(62, -4, 36, 36)])
            <path d="M14 0v12M146 0v16" stroke="#8C6A4A" stroke-width="1"/>
            @include('decor.parts.bauble', ['attrs' => $at(4, 0, 20, 32), 'tone' => 'red', 'pattern' => 0])
            @include('decor.parts.bauble', ['attrs' => $at(136, 2, 20, 32), 'tone' => 'blue', 'pattern' => 1])
            @include('decor.parts.gift', ['attrs' => $at(28, 44, 52, 49), 'tone' => 'wine'])
            @include('decor.parts.gift', ['attrs' => $at(74, 56, 38, 36), 'tone' => 'forest'])
            @include('decor.parts.gift', ['attrs' => $at(106, 50, 44, 42), 'tone' => 'cream'])
            <path d="M0 92c50-5 110-5 160 0v8H0z" fill="#fff" stroke="#D6E4F0" stroke-width="1"/>
            <g fill="#fff" stroke="#BFD3E6" stroke-width=".6">
                @foreach ([[40, 22], [118, 30], [100, 14], [26, 40], [140, 46]] as [$x, $y])
                    <circle cx="{{ $x }}" cy="{{ $y }}" r="2"/>
                @endforeach
            </g>
            @break
    @endswitch
</svg>
