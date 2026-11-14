<x-app-layout>
    <x-slot name="title">Find readers · Bookworm</x-slot>
    <x-slot name="header">
        <x-page-header eyebrow="Bookworm community" title="Find readers" subtitle="Start typing a name or username — the best matches appear right away.">
            <x-slot name="actions">
                <a href="{{ route('books.index') }}" class="btn-ghost"><span aria-hidden="true">&larr;</span> Explore books</a>
            </x-slot>
        </x-page-header>
    </x-slot>

    <div class="page-section space-y-10">
        <form method="GET" action="{{ route('users.index') }}" class="bw-search" role="search">
            <label for="reader-search" class="sr-only">Name or username</label>
            <svg class="bw-search__icon" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M8.5 3a5.5 5.5 0 1 0 3.46 9.78l3.63 3.63a.75.75 0 1 0 1.06-1.06l-3.63-3.63A5.5 5.5 0 0 0 8.5 3ZM4.5 8.5a4 4 0 1 1 8 0 4 4 0 0 1-8 0Z" clip-rule="evenodd"/></svg>
            <input id="reader-search" type="search" name="q" value="{{ $query }}" placeholder="Search names or usernames" autocomplete="off" class="bw-search__input" data-reader-suggest autofocus>
            <button type="submit" class="btn-primary shrink-0">Search readers</button>
        </form>

        @if ($query !== '')
            <section aria-labelledby="reader-results-heading">
                <div class="mb-5 flex flex-wrap items-baseline justify-between gap-3">
                    <h2 id="reader-results-heading" class="font-display text-3xl text-ink dark:text-[#f4e9e2] font-bold">Search results</h2>
                    <span class="text-sm text-tan">{{ $readers->total() }} {{ Str::plural('reader', $readers->total()) }} for “{{ $query }}”</span>
                </div>
                <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @forelse ($readers as $reader)
                        <x-reader-card :reader="$reader" />
                    @empty
                        <x-empty-state class="sm:col-span-2 lg:col-span-3" title="No readers found" message="Try another name or username." />
                    @endforelse
                </div>
                @if ($readers->hasPages())
                    <div class="mt-6">{{ $readers->links() }}</div>
                @endif
            </section>
        @else
            <x-empty-state title="Find your reading people" message="Search by name or username to discover readers whose reviews make you pause." />
        @endif

        @if ($following->isNotEmpty())
            <section aria-labelledby="following-heading">
                <p class="eyebrow">Already in your circle</p>
                <h2 id="following-heading" class="font-display mt-1 text-3xl text-ink dark:text-[#f4e9e2] font-bold">Readers you follow</h2>
                <div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    @foreach ($following as $reader)
                        <x-reader-card :reader="$reader" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
</x-app-layout>
