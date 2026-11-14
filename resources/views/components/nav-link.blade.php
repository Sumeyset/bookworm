@props(['active'])

@php
$classes = ($active ?? false)
            ? 'inline-flex items-center rounded-full bg-rosetint px-4 py-2 text-sm font-semibold leading-5 text-burgundy dark:bg-[#382329] dark:text-burgundy-dark'
            : 'inline-flex items-center rounded-full px-4 py-2 text-sm font-semibold leading-5 text-tan transition duration-150 ease-out hover:bg-parchment hover:text-burgundy dark:text-[#c7a491] dark:hover:bg-[#382329] dark:hover:text-[#F4E9E2]';
@endphp

<a {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</a>
