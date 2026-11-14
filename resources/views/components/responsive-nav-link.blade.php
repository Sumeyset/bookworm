@props(['active'])

@php
$classes = ($active ?? false)
            ? 'block w-full rounded-lg bg-rosetint px-4 py-2 text-start text-base font-semibold text-burgundy dark:bg-[#382329] dark:text-burgundy-dark'
            : 'block w-full rounded-lg px-4 py-2 text-start text-base font-medium text-tan transition duration-150 ease-out hover:bg-parchment hover:text-burgundy dark:text-[#c7a491] dark:hover:bg-[#382329] dark:hover:text-[#F4E9E2]';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
