@props(['title', 'message'])

<div {{ $attributes->class(['rounded-xl border border-dashed border-warmborder bg-paper/80 px-5 py-10 text-center dark:border-[#4B3035] dark:bg-raised/80']) }}>
    <x-logo variant="icon" tone="dark" class="mx-auto h-14 w-14" />
    <h3 class="mt-4 font-serif text-2xl font-semibold text-burgundy dark:text-[#F4E9E2]">{{ $title }}</h3>
    <p class="mx-auto mt-2 max-w-md text-sm leading-6 text-tan dark:text-[#c7a491]">{{ $message }}</p>
    @if (isset($slot) && trim((string) $slot) !== '')
        <div class="mt-5">{{ $slot }}</div>
    @endif
</div>
