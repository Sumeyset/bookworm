@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'rounded-lg border-warmborder bg-paper text-ink shadow-sm placeholder:text-tan focus:border-burgundy focus:ring-burgundy/20 dark:border-[#4B3035] dark:bg-[#201317] dark:text-[#F4E9E2] dark:placeholder:text-[#c7a491] dark:focus:border-burgundy-dark dark:focus:ring-burgundy-dark/25']) }}>
