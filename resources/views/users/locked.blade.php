<x-app-layout>
    <x-slot name="title">{{ $profile->name }} · Bookworm</x-slot>

    <div class="page-section space-y-10">
        @include('users.partials.hero', ['locked' => true])

        <section class="bw-card mx-auto max-w-xl p-10 text-center">
            <p class="text-5xl" aria-hidden="true">🔒</p>
            <h2 class="mt-3 font-display text-4xl font-bold text-ink dark:text-[#F4E9E2]">This account is private</h2>
            <p class="mt-2 text-walnut dark:text-[#d4c2b9]">
                @if ($requested)
                    You asked to follow {{ Str::before($profile->name, ' ') }}. Their shelf, posts and reading life will show here once they say yes.
                @else
                    Follow {{ Str::before($profile->name, ' ') }} to see their shelf, posts and reading life.
                @endif
            </p>
        </section>
    </div>
</x-app-layout>
