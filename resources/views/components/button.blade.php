@props(['variant' => 'primary', 'type' => 'button'])

@php
    $variants = [
        'primary' => 'bg-burgundy text-[#FFF8EF] shadow-sm hover:bg-burgundy-hover active:bg-burgundy-deep',
        'secondary' => 'border border-burgundy/35 bg-paper text-burgundy hover:bg-rosetint dark:border-burgundy-dark/50 dark:bg-raised dark:text-[#F4E9E2] dark:hover:bg-[#382329]',
        'ghost' => 'text-burgundy hover:bg-rosetint dark:text-[#F4E9E2] dark:hover:bg-[#382329]',
    ];
@endphp

<button type="{{ $type }}" {{ $attributes->class(['focus-ring inline-flex min-h-11 items-center justify-center rounded-full px-5 py-2.5 text-sm font-semibold transition duration-200 active:translate-y-px', $variants[$variant] ?? $variants['primary']]) }}>
    {{ $slot }}
</button>
