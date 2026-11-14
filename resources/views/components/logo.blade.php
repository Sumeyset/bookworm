@props(['variant' => 'full', 'tone' => 'dark', 'size' => 'md'])

{{--
    Bookworm logo, using the brand image (public/images/brand/).
    - variant="image": the full logo card exactly as designed (for light backgrounds)
    - variant="icon":  the round emblem cut from that image
    - variant="full":  the emblem with the "Bookworm" wordmark beside it
--}}
@php
    $isLight = $tone === 'light';
    $sizeClasses = match ($size) {
        'sm' => 'h-8 w-8',
        'lg' => 'h-12 w-12 sm:h-14 sm:w-14',
        'xl' => 'h-20 w-20 sm:h-24 sm:w-24',
        'fill' => 'h-full w-auto',
        default => 'h-10 w-10 sm:h-11 sm:w-11',
    };
@endphp

@if ($variant === 'image')
    {{-- Framed to the white card inside the image (pixels 42–518), so the beige border doesn't show --}}
    <span {{ $attributes->class(['relative block aspect-square select-none overflow-hidden rounded-[6%] shadow-[0_24px_50px_-24px_rgba(26,15,18,0.7)]']) }}>
        <picture>
            <source srcset="{{ asset('images/brand/bookworm-logo.webp') }}" type="image/webp">
            <img src="{{ asset('images/brand/bookworm-logo.png') }}" alt="Bookworm — Living library" width="560" height="560" decoding="async"
                 class="absolute max-w-none" style="width: 117.65%; left: -8.82%; top: -8.82%;">
        </picture>
    </span>
@else
    <span {{ $attributes->class(['bw-logo inline-flex items-center gap-3 select-none']) }}>
        <picture class="bw-logo__mark {{ $sizeClasses }} shrink-0">
            <source srcset="{{ asset('images/brand/bookworm-mark.webp') }}" type="image/webp">
            <img src="{{ asset('images/brand/bookworm-mark.png') }}" alt="" width="249" height="249" class="h-full w-full rounded-full object-contain {{ $isLight ? 'shadow-[0_6px_18px_-6px_rgba(0,0,0,0.6)] ring-1 ring-white/20' : 'shadow-[0_8px_18px_-10px_rgba(74,18,32,0.7)]' }}" decoding="async">
        </picture>

        @if ($variant === 'full')
            <span class="flex min-w-0 flex-col items-start text-left leading-none">
                <span class="font-serif text-[1.55rem] font-bold leading-none tracking-tight {{ $isLight ? 'text-[#FFF8EF]' : 'text-ink dark:text-[#FFF8EF]' }}">Book<span class="{{ $isLight ? 'text-[#F4A9B8]' : 'text-burgundy dark:text-[#F0A8B4]' }}">worm</span></span>
                <span class="mt-1.5 flex items-center gap-1.5 text-[9px] font-semibold uppercase tracking-[0.3em] {{ $isLight ? 'text-[#F1D9C4]/80' : 'text-tan dark:text-[#C7A491]' }}">
                    <span class="h-px w-3 bg-current opacity-60"></span>Living library<span class="h-px w-3 bg-current opacity-60"></span>
                </span>
            </span>
        @endif
    </span>
@endif
