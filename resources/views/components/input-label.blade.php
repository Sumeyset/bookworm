@props(['value'])

<label {{ $attributes->merge(['class' => 'block text-sm font-medium text-ink dark:text-[#F4E9E2]']) }}>
    {{ $value ?? $slot }}
</label>
