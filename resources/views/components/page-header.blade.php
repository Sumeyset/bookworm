@props(['eyebrow' => null, 'title', 'subtitle' => null])

{{-- Shared page heading: small eyebrow, big serif title, optional subtitle and actions --}}
<div {{ $attributes->class(['flex flex-col gap-5 sm:flex-row sm:items-end sm:justify-between']) }}>
    <div class="min-w-0">
        @if ($eyebrow)
            <p class="eyebrow flex items-center gap-2"><span class="h-px w-6 bg-gold/70" aria-hidden="true"></span>{{ $eyebrow }}</p>
        @endif
        <h1 class="font-display mt-3 break-words text-4xl leading-none text-ink dark:text-[#F4E9E2] sm:text-5xl lg:text-6xl font-bold">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-3 max-w-2xl text-base leading-7 text-walnut dark:text-[#d4c2b9] sm:text-lg">{{ $subtitle }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex shrink-0 flex-wrap items-center gap-2">{{ $actions }}</div>
    @endisset
</div>
