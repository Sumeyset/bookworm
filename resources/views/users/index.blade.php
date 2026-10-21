<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#9b7466] dark:text-[#c7a491]">Bookworm community</p>
                <h1 class="mt-1 text-2xl font-semibold text-[#392b2a] dark:text-[#f4e9e2]">Find readers</h1>
            </div>
            <a href="{{ route('books.index') }}" class="text-sm font-medium text-[#713640] hover:underline dark:text-[#e4b3a5]">
                &larr; Explore books
            </a>
        </div>
    </x-slot>

    <main class="py-8 sm:py-10">
        <div class="mx-auto max-w-4xl space-y-6 px-4">
            <section class="rounded-2xl border border-[#eadfd4] bg-[#fffdf9] p-5 shadow-sm sm:p-6 dark:border-[#453538] dark:bg-[#291f20]">
                <p class="mb-4 text-sm text-[#8a7065] dark:text-[#c1aaa0]">Search by name or username to find readers and follow their book reviews.</p>
                <form method="GET" action="{{ route('users.index') }}" class="flex flex-col gap-3 sm:flex-row">
                    <label for="reader-search" class="sr-only">Name or username</label>
                    <input id="reader-search" type="search" name="q" value="{{ $query }}"
                           placeholder="Search names or usernames"
                           class="min-w-0 flex-1 rounded-xl border-[#dfd2c6] bg-[#fffdf9] text-sm text-[#392b2a] shadow-sm placeholder:text-[#a99487] focus:border-[#9b5b54] focus:ring-[#9b5b54] dark:border-[#594749] dark:bg-[#20191a] dark:text-[#f4e9e2]"
                           autocomplete="off">
                    <button type="submit" class="inline-flex items-center justify-center rounded-xl bg-[#5b1f2b] px-5 py-2.5 text-sm font-semibold text-[#fff8ef] transition hover:bg-[#713640] focus:outline-none focus:ring-2 focus:ring-[#b98768] focus:ring-offset-2 dark:focus:ring-offset-[#291f20]">
                        Search readers
                    </button>
                </form>
            </section>

            <section aria-labelledby="reader-results-heading">
                <div class="mb-4 flex flex-wrap items-baseline justify-between gap-3">
                    <h2 id="reader-results-heading" class="text-xl font-semibold text-[#392b2a] dark:text-[#f4e9e2]">
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
                        <div class="rounded-2xl border border-dashed border-[#d9c6b6] bg-[#fffdf9] px-5 py-10 text-center dark:border-[#594749] dark:bg-[#291f20] sm:col-span-2">
                            <p class="font-handwritten text-2xl font-semibold text-[#713640] dark:text-[#f0d8c1]">Find your reading people</p>
                            <p class="mt-1 text-sm text-[#8a7065] dark:text-[#c1aaa0]">Search by name or username to discover readers.</p>
                        </div>
                    @else
                        @forelse ($readers as $reader)
                        <article class="flex min-w-0 items-center gap-4 rounded-2xl border border-[#eadfd4] bg-[#fffdf9] p-4 shadow-sm dark:border-[#453538] dark:bg-[#291f20]">
                            @if ($reader->profile_photo_url)
                                <img src="{{ $reader->profile_photo_url }}" alt="" class="h-12 w-12 shrink-0 rounded-xl object-cover">
                            @else
                                <span class="flex h-12 w-12 shrink-0 items-center justify-center rounded-xl bg-[#f1e3d6] font-handwritten text-2xl font-bold text-[#713640] dark:bg-[#493536] dark:text-[#f0d8c1]" aria-hidden="true">
                                    {{ strtoupper(substr($reader->name, 0, 1)) }}
                                </span>
                            @endif
                            <div class="min-w-0 flex-1">
                                <a href="{{ route('users.show', $reader->username) }}"
                                   class="block truncate font-semibold text-[#392b2a] hover:text-[#713640] hover:underline focus:outline-none focus:ring-2 focus:ring-[#b98768] dark:text-[#f4e9e2] dark:hover:text-[#e4b3a5]">
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
                               class="shrink-0 rounded-full border border-[#c9a58f] px-3 py-1.5 text-xs font-semibold text-[#713640] transition hover:bg-[#faf2e9] focus:outline-none focus:ring-2 focus:ring-[#b98768] dark:border-[#765a58] dark:text-[#f0d8c1] dark:hover:bg-[#382c2d]">
                                View
                            </a>
                        </article>
                        @empty
                            <div class="rounded-2xl border border-dashed border-[#d9c6b6] bg-[#fffdf9] px-5 py-10 text-center dark:border-[#594749] dark:bg-[#291f20] sm:col-span-2">
                                <p class="font-handwritten text-2xl font-semibold text-[#713640] dark:text-[#f0d8c1]">No readers found</p>
                                <p class="mt-1 text-sm text-[#8a7065] dark:text-[#c1aaa0]">Try another name or username.</p>
                            </div>
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
