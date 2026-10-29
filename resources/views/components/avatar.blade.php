@props(['src' => null, 'name' => 'Reader', 'size' => 'md', 'tone' => 'dark'])

@php
    $sizes = ['sm' => 'h-9 w-9 text-base', 'reader' => 'h-12 w-12 text-xl', 'md' => 'h-16 w-16 text-2xl', 'lg' => 'h-24 w-24 text-4xl'];
    $sizeClasses = $sizes[$size] ?? $sizes['md'];
    $ring = $tone === 'light'
        ? 'ring-2 ring-[#F4E9E2]/65 ring-offset-2 ring-offset-burgundy-deep'
        : 'ring-2 ring-burgundy/70 ring-offset-2 ring-offset-paper dark:ring-burgundy-dark dark:ring-offset-raised';
@endphp

@if ($src)
    <img src="{{ $src }}" alt="{{ $name }}'s profile photo" {{ $attributes->class([$sizeClasses, $ring, 'shrink-0 rounded-full object-cover']) }}>
@else
    <span aria-hidden="true" {{ $attributes->class([$sizeClasses, $ring, 'flex shrink-0 items-center justify-center rounded-full bg-parchment font-serif font-semibold text-burgundy dark:bg-[#493536] dark:text-[#F4E9E2]']) }}>
        {{ strtoupper(substr($name, 0, 1)) }}
    </span>
@endif
