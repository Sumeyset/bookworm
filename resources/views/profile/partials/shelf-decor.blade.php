{{-- A little object that sits on a shelf next to the books. Expects $kind and (for "letter") $initial. --}}
<span class="shelf-decor shelf-decor--{{ $kind }}" aria-hidden="true">
    @switch($kind)
        @case('plant')
            {{-- trailing pothos in a white pot --}}
            <svg viewBox="0 0 80 110"><path d="M18 40h44l-5 32H23z" fill="#F7F3EE"/><path d="M18 40h44l-1 6H19z" fill="#E6DED3"/>
                <g fill="#5E9A4E"><ellipse cx="30" cy="34" rx="9" ry="6" transform="rotate(-30 30 34)"/><ellipse cx="44" cy="28" rx="9" ry="6" transform="rotate(20 44 28)"/><ellipse cx="54" cy="36" rx="8" ry="5" transform="rotate(40 54 36)"/><ellipse cx="38" cy="22" rx="7" ry="5" transform="rotate(-10 38 22)"/></g>
                <path d="M22 44c-6 14-4 30-10 46M60 46c6 16 2 34 8 52" fill="none" stroke="#4E8A40" stroke-width="2"/>
                <g fill="#6BAA58"><ellipse cx="16" cy="60" rx="5" ry="3.5" transform="rotate(50 16 60)"/><ellipse cx="13" cy="76" rx="5" ry="3.5" transform="rotate(-40 13 76)"/><ellipse cx="64" cy="66" rx="5" ry="3.5" transform="rotate(-50 64 66)"/><ellipse cx="67" cy="84" rx="5" ry="3.5" transform="rotate(40 67 84)"/><ellipse cx="66" cy="98" rx="4.5" ry="3" transform="rotate(-30 66 98)"/></g></svg>
            @break
        @case('succulent')
            {{-- succulent in a white geometric pot --}}
            <svg viewBox="0 0 70 70"><path d="M14 36h42l-6 30H20z" fill="#FFFFFF"/><path d="M14 36l8 10 6-10 7 10 7-10 6 10 8-10" fill="none" stroke="#E2DAD0" stroke-width="1.4"/><path d="M20 52l8 14M50 52l-8 14M35 46v20" stroke="#E2DAD0" stroke-width="1.2"/>
                <g fill="#7FB37A"><path d="M35 36c-3-10 0-18 0-18s3 8 0 18z"/><path d="M35 36c-10-4-14-12-14-12s9 2 14 12z"/><path d="M35 36c10-4 14-12 14-12s-9 2-14 12z"/><path d="M35 36c-12 0-18-6-18-6s10-2 18 6z" fill="#6AA164"/><path d="M35 36c12 0 18-6 18-6s-10-2-18 6z" fill="#6AA164"/></g></svg>
            @break
        @case('candles')
            {{-- two copper candle holders --}}
            <svg viewBox="0 0 80 60"><defs><linearGradient id="cu-{{ $loop->index ?? 0 }}{{ $kind }}" x1="0" x2="1"><stop offset="0" stop-color="#B8693D"/><stop offset=".5" stop-color="#F0B48A"/><stop offset="1" stop-color="#A65A30"/></linearGradient></defs>
                <rect x="8" y="22" width="26" height="36" rx="3" fill="#C27A4C"/><rect x="44" y="30" width="26" height="28" rx="3" fill="#C27A4C"/>
                <rect x="8" y="22" width="26" height="36" rx="3" fill="url(#cu-{{ $loop->index ?? 0 }}{{ $kind }})" opacity=".8"/><rect x="44" y="30" width="26" height="28" rx="3" fill="url(#cu-{{ $loop->index ?? 0 }}{{ $kind }})" opacity=".8"/>
                <ellipse class="shelf-decor__flame" cx="21" cy="17" rx="3" ry="6" fill="#FFC14D"/><ellipse class="shelf-decor__flame" cx="57" cy="25" rx="3" ry="6" fill="#FFC14D"/></svg>
            @break
        @case('candle')
            <svg viewBox="0 0 40 70"><rect x="10" y="26" width="20" height="40" rx="2" fill="#FBF6EE"/><rect x="6" y="62" width="28" height="6" rx="2" fill="#C99A45"/><path d="M20 26v-6" stroke="#2A1A1D" stroke-width="1.4"/><ellipse class="shelf-decor__flame" cx="20" cy="14" rx="4" ry="8" fill="#FFC14D"/></svg>
            @break
        @case('globe')
            <svg viewBox="0 0 70 90"><circle cx="35" cy="36" r="24" fill="#7FA9C9"/><path d="M22 26c6 2 8-4 14-2s4 10 10 8M18 42c8-2 10 6 18 4M40 50c4-4 10-2 12-6" fill="none" stroke="#C9A462" stroke-width="5" stroke-linecap="round"/><path d="M35 8a28 28 0 0 1 0 56" fill="none" stroke="#B8893B" stroke-width="2.5"/><path d="M35 64v14M22 82h26" stroke="#8A5A36" stroke-width="5" stroke-linecap="round"/></svg>
            @break
        @case('frame')
            <svg viewBox="0 0 70 80"><rect x="6" y="6" width="58" height="70" rx="2" fill="#C99A45"/><rect x="12" y="12" width="46" height="58" fill="#EADFCF"/><path d="M12 56l14-14 10 10 8-6 14 12v12H12z" fill="#8FB4A2"/><circle cx="44" cy="28" r="6" fill="#F2C38B"/></svg>
            @break
        @case('mug')
            <svg viewBox="0 0 60 60"><path d="M10 20h32v28a8 8 0 0 1-8 8H18a8 8 0 0 1-8-8z" fill="#A3324A"/><path d="M42 26h5a7 7 0 0 1 0 14h-5" fill="none" stroke="#A3324A" stroke-width="4"/><path d="M20 12c-3-4 3-6 0-10M30 12c-3-4 3-6 0-10" fill="none" stroke="#D9C6B6" stroke-width="2" stroke-linecap="round" class="shelf-decor__steam"/><path d="M16 34h20" stroke="#fff" stroke-width="1.5" opacity=".6"/></svg>
            @break
        @case('cactus')
            {{-- gold cactus --}}
            <svg viewBox="0 0 60 90"><defs><linearGradient id="gold-{{ $kind }}" x1="0" x2="1"><stop offset="0" stop-color="#B8893B"/><stop offset=".5" stop-color="#F6DC9A"/><stop offset="1" stop-color="#A87A2C"/></linearGradient></defs><path d="M24 86V30a6 6 0 0 1 12 0v56z M24 56h-6a6 6 0 0 1-6-6V38a4 4 0 0 1 8 0v10h4z M36 48h6V30a4 4 0 0 1 8 0v14a6 6 0 0 1-6 6h-8z" fill="url(#gold-{{ $kind }})"/></svg>
            @break
        @case('bowl')
            <svg viewBox="0 0 80 40"><path d="M6 10h68c0 16-14 26-34 26S6 26 6 10z" fill="#B07A4C"/><path d="M6 10h68" stroke="#8A5A36" stroke-width="3"/><path d="M14 18c8 8 20 10 30 9" fill="none" stroke="#D6A576" stroke-width="2" opacity=".7"/></svg>
            @break
        @case('painting')
            <svg viewBox="0 0 60 60"><rect x="4" y="4" width="52" height="52" fill="#F7F3EE"/><rect x="8" y="8" width="44" height="44" fill="#7FB6C9"/><path d="M8 34c8-6 14 2 22-4s14 0 22-4v26H8z" fill="#2F7A8C"/><path d="M8 42c10-4 16 4 26-2s12 0 18-2v14H8z" fill="#E8DCC4"/></svg>
            @break
        @case('letter')
            {{-- marquee letter lamp with the reader's initial --}}
            <span class="shelf-decor__letter">{{ $initial }}</span>
            @break
        @case('shell')
            <svg viewBox="0 0 50 36"><path d="M25 32C8 32 2 20 6 12 12 2 38 2 44 12c4 8-2 20-19 20z" fill="#F4E9DD"/><path d="M25 32L12 8M25 32l-4-26M25 32l4-26M25 32l13-24" stroke="#D9C6B6" stroke-width="1.4"/></svg>
            @break
        @case('lantern')
            <svg viewBox="0 0 50 80"><path d="M25 2v8" stroke="#2A1A1D" stroke-width="2"/><path d="M14 14h22l4 8v44l-4 8H14l-4-8V22z" fill="#2A1A1D"/><rect x="14" y="24" width="22" height="40" fill="#FFD887" class="shelf-decor__glow"/><path d="M25 24v40M14 44h22" stroke="#2A1A1D" stroke-width="2"/></svg>
            @break
    @endswitch
</span>
