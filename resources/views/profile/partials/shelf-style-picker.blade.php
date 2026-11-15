{{-- Pick how your shelf looks. Expects $current (style key). --}}
@php
    $thumbs = [
        'tree' => [asset(\App\Support\TreeBackdrop::IMAGE), '52% 35%'],
        'christmas' => [asset('images/shelves/christmas-tree-shelf.webp'), '50% 45%'],
        'wall' => [asset('images/shelves/wall-shelf.webp'), '50% 50%'],
        'window' => [asset('images/shelves/window-nook.webp'), '50% 40%'],
    ];
@endphp
<dialog id="shelf-style-dialog" class="bw-dialog shelf-styles" aria-labelledby="shelf-style-title">
    <div class="p-5 sm:p-6">
        <div class="flex items-start justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-tan dark:text-[#c7a491]">Make it yours</p>
                <h4 id="shelf-style-title" class="mt-1 font-serif text-2xl font-semibold text-ink dark:text-[#f4e9e2]">Shelf style</h4>
                <p class="mt-1 text-sm text-walnut dark:text-[#d4c2b9]">Visitors see your shelf in the style you pick. Your books stay on the same shelves.</p>
            </div>
            <button type="button" class="ig-icon !h-9 !w-9" data-close-shelf-styles aria-label="Close"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg></button>
        </div>

        <div class="mt-5 grid grid-cols-2 gap-3">
            @foreach (\App\Support\ShelfStyle::STYLES as $key => [$name, $description])
                <form method="POST" action="{{ route('shelf.style') }}" data-scroll-top>
                    @csrf
                    <input type="hidden" name="style" value="{{ $key }}">
                    <button type="submit" class="shelf-style-card {{ $current === $key ? 'is-current' : '' }}" @if ($current === $key) aria-current="true" @endif>
                        <span class="shelf-style-thumb" style="--thumb: url('{{ $thumbs[$key][0] }}'); --thumb-pos: {{ $thumbs[$key][1] }}; {{ $key === 'window' ? 'background-color: #9fc0dc;' : '' }}" aria-hidden="true"></span>
                        <span class="mt-2 block font-semibold text-ink dark:text-[#f4e9e2]">{{ $name }} @if ($current === $key)<span class="text-xs font-semibold text-burgundy dark:text-[#E9A2AE]">· Current</span>@endif</span>
                        <span class="mt-0.5 block text-xs leading-snug text-tan">{{ $description }}</span>
                    </button>
                </form>
            @endforeach
        </div>
    </div>
</dialog>
