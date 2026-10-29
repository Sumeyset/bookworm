<button {{ $attributes->merge(['type' => 'button', 'class' => 'focus-ring inline-flex min-h-11 items-center rounded-full border border-burgundy/35 bg-paper px-5 py-2.5 text-sm font-semibold text-burgundy shadow-sm transition duration-200 hover:bg-rosetint active:translate-y-px disabled:opacity-50 dark:border-burgundy-dark/50 dark:bg-raised dark:text-[#F4E9E2] dark:hover:bg-[#382329]']) }}>
    {{ $slot }}
</button>
