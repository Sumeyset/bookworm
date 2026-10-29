<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-tan dark:text-[#c7a491]">Bookworm community</p>
                <h1 class="mt-1 font-serif text-3xl font-semibold text-ink dark:text-[#f4e9e2]">Find readers</h1>
            </div>
            <a href="{{ route('books.index') }}" class="text-sm font-medium text-[#713640] hover:underline dark:text-[#e4b3a5]">
                &larr; Explore books
            </a>
        </div>
    </x-slot>

    <main class="py-8 sm:py-10">
        <div class="mx-auto max-w-4xl space-y-6 px-4">
            <section class="bookplate rounded-xl p-5 sm:p-6">
                <p class="mb-4 text-sm text-tan dark:text-[#c1aaa0]">Search by name or username to find readers and follow their book reviews.</p>
                <form method="GET" action="{{ route('users.index') }}" class="flex flex-col gap-3 sm:flex-row">
                    <label for="reader-search" class="sr-only">Name or username</label>
                    <input id="reader-search" type="search" name="q" value="{{ $query }}"
                           placeholder="Search names or usernames"
                           class="min-w-0 flex-1 rounded-lg border-warmborder bg-paper text-sm text-ink shadow-sm placeholder:text-tan focus:border-burgundy focus:ring-burgundy/20 dark:border-[#4B3035] dark:bg-[#201317] dark:text-[#f4e9e2]"
                           autocomplete="off">
                    <button type="submit" class="focus-ring inline-flex min-h-11 items-center justify-center rounded-full bg-burgundy px-5 py-2.5 text-sm font-semibold text-[#fff8ef] transition hover:bg-burgundy-hover">
                        Search readers
                    </button>
                </form>
            </section>

            <section aria-labelledby="reader-results-heading">
                <div class="mb-4 flex flex-wrap items-baseline justify-between gap-3">
                    <h2 id="reader-results-heading" class="font-serif text-2xl font-semibold text-ink dark:text-[#f4e9e2]">
                        {{ $query === '' ? 'Search for readers' : 'Search results' }}
                    </h2>
                    @if ($query !== '')
                        <span class="text-sm text-[#8a7065] dark:text-[#c1aaa0]">
                            {{ $readers->total() }} {{ Str::plural('reader', $readers->total()) }}
                        </span>
                    @endif
                </div>

                <div class="grid gap-3 sm:grid-cols-2">
                    @if ($query === '')
                        <x-empty-state class="sm:col-span-2" title="Find your reading people" message="Search by name or username to discover readers." />
                    @else
                        @forelse ($readers as $reader)
                        <article class="bookplate flex min-w-0 items-center gap-4 rounded-xl p-4">
                            <x-avatar :src="$reader->profile_photo_url" :name="$reader->name" size="reader" />
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('users.show', $reader->username) }}"
                                   class="focus-ring block truncate font-serif font-semibold text-ink hover:text-burgundy hover:underline dark:text-[#f4e9e2] dark:hover:text-burgundy-dark">
                                    {{ $reader->name }}
                                </a>
                                <p class="truncate text-sm text-[#8a7065] dark:text-[#c1aaa0]">{{ '@' . $reader->username }}</p>
                                <p class="mt-1 text-xs text-[#9a8579] dark:text-[#b39d93]">
                                    {{ $reader->followers_count }} {{ Str::plural('follower', $reader->followers_count) }}
                                    <span aria-hidden="true">&middot;</span>
                                    {{ $reader->reviews_count }} {{ Str::plural('review', $reader->reviews_count) }}
                                </p>
                            </div>
                            <a href="{{ route('users.show', $reader->username) }}"
                               aria-label="View {{ $reader->name }}'s profile"
                               class="focus-ring shrink-0 rounded-full border border-burgundy/35 px-3 py-1.5 text-xs font-semibold text-burgundy transition hover:bg-rosetint dark:border-burgundy-dark/50 dark:text-[#f0d8c1] dark:hover:bg-[#382c2d]">
                                View
                            </a>
                        </article>
                        @empty
                            <x-empty-state class="sm:col-span-2" title="No readers found" message="Try another name or username." />
                        @endforelse
                    @endif
                </div>

                @if ($query !== '' && $readers->hasPages())
                    <div class="mt-6">{{ $readers->links() }}</div>
                @endif
            </section>
        </div>
    </main>
</x-app-layout>
