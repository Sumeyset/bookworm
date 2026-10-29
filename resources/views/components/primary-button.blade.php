<button {{ $attributes->merge(['type' => 'submit', 'class' => 'focus-ring inline-flex min-h-11 items-center rounded-full border border-transparent bg-burgundy px-5 py-2.5 text-sm font-semibold text-[#FFF8EF] shadow-sm transition duration-200 hover:bg-burgundy-hover active:bg-burgundy-deep']) }}>
    {{ $slot }}
</button>
