<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex min-h-11 items-center rounded-full border border-transparent bg-red-700 px-5 py-2.5 text-sm font-semibold text-white transition duration-200 hover:bg-red-800 active:bg-red-900 focus:outline-none focus-visible:ring-2 focus-visible:ring-red-700 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-raised']) }}>
    {{ $slot }}
</button>
