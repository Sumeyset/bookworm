<x-app-layout>
    <div class="mx-auto flex min-h-[65vh] max-w-3xl items-center px-4 py-12">
        <article class="bookplate relative w-full overflow-hidden rounded-xl px-7 py-10 sm:px-14 sm:py-16">
            <span class="absolute left-0 top-10 bottom-10 w-1 bg-gradient-to-b from-transparent via-gold to-transparent" aria-hidden="true"></span>
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-tan dark:text-[#c7a491]">A passage worth keeping</p>
            <blockquote class="mt-5 font-serif text-2xl italic leading-relaxed text-ink dark:text-[#F4E9E2] sm:text-4xl">“{{ $quote->highlight->text }}”</blockquote>
            <div class="mt-8 border-t border-warmborder pt-5 dark:border-[#4B3035]">
                <p class="font-serif text-xl font-semibold text-burgundy dark:text-burgundy-dark">{{ $quote->highlight->bookPdf->book->title }}</p>
                <p class="mt-1 text-sm text-tan dark:text-[#c7a491]">{{ $quote->highlight->bookPdf->book->author_name ?? 'Unknown author' }} · Page {{ $quote->highlight->page_number }}</p>
                <p class="mt-4 text-xs text-tan dark:text-[#c7a491]">Kept by {{ $quote->user->name }} · Bookworm</p>
            </div>
        </article>
    </div>
</x-app-layout>
