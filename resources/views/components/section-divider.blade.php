@props(['label' => null])

<div {{ $attributes->class(['flex items-center gap-3 text-gold']) }} aria-hidden="true">
    <span class="h-px flex-1 bg-gradient-to-r from-transparent via-gold/50 to-transparent"></span>
    <span class="font-serif text-sm">{{ $label ?? '❧' }}</span>
    <span class="h-px flex-1 bg-gradient-to-r from-transparent via-gold/50 to-transparent"></span>
</div>
