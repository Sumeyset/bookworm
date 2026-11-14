<x-app-layout>
    <x-slot name="title">Reading feed · Bookworm</x-slot>
    <x-slot name="header">
        <x-page-header eyebrow="Notes from your reading circle" title="Reading feed" subtitle="Thoughts and quotes from your reading circle. What friends start, finish and rate shows up in your notifications." />
    </x-slot>

    <div class="page-section grid justify-center gap-8 lg:grid-cols-[minmax(0,30rem)_20rem]">
        <div class="mx-auto w-full min-w-0 max-w-[30rem] space-y-5">
            {{-- Create a post --}}
            <section id="composer" class="bw-card scroll-mt-28 p-4" aria-label="Create a post">
                <div class="flex items-center gap-3">
                    <x-avatar :src="auth()->user()->profile_photo_url" :name="auth()->user()->name" size="sm" />
                    <button type="button" data-open-composer="thought" class="focus-ring min-h-11 flex-1 rounded-full border border-warmborder bg-parchment/40 px-4 text-left text-sm text-tan transition hover:border-burgundy/40 dark:border-[#4B3035] dark:bg-white/5">
                        What are you reading, {{ Str::before(auth()->user()->name, ' ') }}?
                    </button>
                </div>
                <div class="mt-3 grid grid-cols-2 gap-2">
                    <button type="button" data-open-composer="quote" class="focus-ring flex min-h-11 items-center justify-center gap-2 rounded-2xl text-sm font-semibold text-walnut transition hover:bg-parchment dark:text-[#e3d3ca] dark:hover:bg-white/5">
                        <span class="flex h-7 w-6 items-center justify-center rounded-md bg-gradient-to-br from-[#4A1220] to-[#8B2A3D] font-serif text-lg leading-none text-[#FFF8EF]" aria-hidden="true">“</span>
                        Quote card
                    </button>
                    <button type="button" data-open-composer="thought" class="focus-ring flex min-h-11 items-center justify-center gap-2 rounded-2xl text-sm font-semibold text-walnut transition hover:bg-parchment dark:text-[#e3d3ca] dark:hover:bg-white/5">
                        <span class="flex h-7 w-6 items-center justify-center rounded-md bg-gradient-to-br from-[#ffcf9f] via-[#f58f7c] to-[#6c3b85] text-xs text-white" aria-hidden="true">Aa</span>
                        Thought
                    </button>
                </div>
            </section>

            @include('feed.partials.composer')

            {{-- Scope tabs --}}
            <nav class="flex items-center gap-2" aria-label="Feed filter">
                @foreach (['following' => 'Following', 'everyone' => 'Everyone'] as $key => $label)
                    <a href="{{ route('feed.index', $key === 'everyone' ? ['scope' => 'everyone'] : []) }}" @if ($scope === $key) aria-current="page" @endif
                       class="{{ $scope === $key ? 'border-burgundy bg-burgundy text-[#FFF8EF] dark:border-burgundy-dark dark:bg-burgundy-dark' : 'border-warmborder text-walnut hover:border-burgundy/40 dark:border-[#4B3035] dark:text-[#e3d3ca]' }} focus-ring rounded-full border px-4 py-2 text-sm font-semibold transition">{{ $label }}</a>
                @endforeach
            </nav>

            @forelse ($items as $item)
                @include('feed.partials.card', ['item' => $item])
            @empty
                <x-empty-state title="Your reading circle is quiet" message="Write the first post above, or follow a few readers to see their reviews and quotes here.">
                    <a href="{{ route('users.index') }}" class="btn-primary">Find readers</a>
                </x-empty-state>
            @endforelse

            @if ($nextBefore)
                <div class="text-center">
                    <a href="{{ route('feed.index', array_filter(['scope' => $scope === 'everyone' ? 'everyone' : null, 'before' => $nextBefore])) }}" class="btn-ghost">Load older posts</a>
                </div>
            @endif
        </div>

        <aside class="space-y-5 lg:sticky lg:top-28 lg:self-start">
            <div class="bw-card p-5">
                <p class="eyebrow">Your circle</p>
                <h2 class="mt-1 font-serif text-2xl font-semibold text-ink dark:text-[#F4E9E2]">Readers you follow</h2>
                @if ($following->isEmpty())
                    <p class="mt-3 text-sm leading-6 text-walnut dark:text-[#d4c2b9]">You’re not following anyone yet.</p>
                @else
                    <ul class="mt-4 space-y-1">
                        @foreach ($following as $reader)
                            <li>
                                <a href="{{ route('users.show', $reader->username) }}" class="focus-ring flex items-center gap-3 rounded-2xl p-2 transition hover:bg-parchment/70 dark:hover:bg-white/5">
                                    <x-avatar :src="$reader->profile_photo_url" :name="$reader->name" size="sm" />
                                    <span class="min-w-0 flex-1">
                                        <span class="block truncate text-sm font-semibold text-ink dark:text-[#F4E9E2]">{{ $reader->name }}</span>
                                        <span class="block text-xs text-tan">{{ $reader->reviews_count }} {{ Str::plural('review', $reader->reviews_count) }}</span>
                                    </span>
                                </a>
                            </li>
                        @endforeach
                    </ul>
                @endif
                <a href="{{ route('users.index') }}" class="btn-ghost mt-4 w-full">Find more readers</a>
            </div>
            <a href="{{ route('quotes.index') }}" class="group block rounded-3xl bg-gradient-to-br from-[#4A1220] to-[#8B2A3D] p-5 text-[#FFF8EF] shadow-bookplate transition hover:-translate-y-0.5">
                <p class="font-display text-3xl font-bold">The quote wall</p>
                <p class="mt-1 text-sm text-[#F1D9C4]/85">Every line readers have kept and shared, in one place.</p>
                <span class="mt-4 inline-flex rounded-full bg-white/15 px-4 py-2 text-sm font-semibold backdrop-blur transition group-hover:bg-white/25">Browse quotes &rarr;</span>
            </a>
        </aside>
    </div>
</x-app-layout>
