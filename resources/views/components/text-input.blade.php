@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'bw-input dark:placeholder:text-[#c7a491]']) }}>
