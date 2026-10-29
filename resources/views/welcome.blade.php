<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-end justify-between gap-4">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-tan dark:text-[#c7a491]">A little thought for today</p>
                <h1 class="mt-1 font-serif text-3xl font-semibold text-ink dark:text-[#F4E9E2]">Welcome, {{ Auth::user()->name }}.</h1>
            </div>
            <a href="{{ route('books.index') }}" class="focus-ring rounded-sm text-sm font-semibold text-burgundy hover:underline dark:text-burgundy-dark">
                Find a book to keep <span aria-hidden="true">&rarr;</span>
            </a>
        </div>
    </x-slot>

    <div class="px-4 py-12 sm:px-6 sm:py-16">
        <div class="mx-auto max-w-4xl">
            @if (! Auth::user()->onboarding_completed_at)
                <aside class="mb-6 flex flex-col justify-between gap-4 rounded-xl border border-burgundy/15 bg-parchment/70 p-5 dark:border-burgundy-dark/30 dark:bg-[#271519] sm:flex-row sm:items-center">
                    <div>
                        <p class="font-serif text-lg font-semibold text-ink dark:text-[#F4E9E2]">Make this reading nook yours.</p>
                        <p class="mt-1 text-sm text-walnut dark:text-[#d4c2b9]">Choose a few favorite genres and set a gentle yearly goal.</p>
                    </div>
                    <a href="{{ route('onboarding.show') }}" class="focus-ring inline-flex shrink-0 rounded-full bg-burgundy px-5 py-2.5 text-sm font-semibold text-white hover:bg-burgundy-hover">Set my preferences</a>
                </aside>
            @endif
            <article class="bookplate relative overflow-hidden rounded-xl px-6 py-10 sm:px-12 sm:py-14" aria-labelledby="daily-quote-title">
                <span class="absolute left-0 top-10 bottom-10 w-1 bg-gradient-to-b from-transparent via-gold/70 to-transparent" aria-hidden="true"></span>
                <div class="mx-auto max-w-2xl text-center">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-tan dark:text-[#c7a491]">{{ now()->format('l, F j') }}</p>
                    <h2 id="daily-quote-title" class="mt-3 font-serif text-2xl font-semibold text-ink dark:text-[#F4E9E2] sm:text-3xl">A little thought for today</h2>

                    @if ($quote)
                        <blockquote class="mt-8 font-serif text-2xl italic leading-relaxed text-walnut dark:text-[#e3d3ca] sm:text-3xl">
                            <span class="block font-serif text-6xl leading-none text-burgundy dark:text-burgundy-dark" aria-hidden="true">“</span>
                            <span class="-mt-5 block">{{ $quote['text'] }}</span>
                        </blockquote>
                        <div class="mx-auto mt-7 h-px w-16 bg-gold/70" aria-hidden="true"></div>
                        <p class="mt-4 font-hand text-2xl text-burgundy dark:text-burgundy-dark">— {{ $quote['author'] }}</p>
                    @elseif ($quoteError)
                        <p class="mt-8 font-serif text-lg italic leading-relaxed text-walnut dark:text-[#d4c2b9]">Today’s thought is taking the scenic route. Try again in a little while.</p>
                    @endif

                    <footer class="mt-9 border-t border-warmborder pt-5 dark:border-[#4B3035]">
                        <p class="text-xs text-tan dark:text-[#c7a491]">
                            Inspirational quotes provided by
                            <a href="https://zenquotes.io/" target="_blank" rel="noopener noreferrer" class="font-semibold text-burgundy underline decoration-gold/70 underline-offset-2 hover:text-burgundy-hover dark:text-burgundy-dark">ZenQuotes API</a>
                        </p>
                    </footer>
                </div>
            </article>
        </div>
    </div>
</x-app-layout>
