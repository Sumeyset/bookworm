<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#9b7466] dark:text-[#c7a491]">Bookworm community</p>
                <h1 class="mt-1 text-2xl font-semibold text-[#392b2a] dark:text-[#f4e9e2]">Reader profile</h1>
            </div>
            <a href="{{ route('books.index') }}" class="text-sm font-medium text-[#713640] hover:underline dark:text-[#e4b3a5]">
                &larr; Explore books
            </a>
        </div>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="mx-auto max-w-4xl space-y-8 px-4">
            @if (session('status'))
                <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-900 dark:bg-green-950 dark:text-green-200" role="status" aria-live="polite">
                    {{ session('status') }}
                </div>
            @endif

            <section aria-labelledby="reader-profile-heading" class="overflow-hidden rounded-2xl border border-[#eadfd4] bg-[#fffdf9] shadow-sm dark:border-[#453538] dark:bg-[#291f20]">
                <div class="h-2 bg-gradient-to-r from-[#713640] via-[#b98768] to-[#e3bd82]"></div>
                <div class="p-5 sm:p-7">
                    <div class="flex flex-col gap-5 sm:flex-row sm:items-start">
                        @if ($profile->profile_photo_url)
                            <img src="{{ $profile->profile_photo_url }}" alt="{{ $profile->name }}'s profile photo" class="h-20 w-20 shrink-0 rounded-2xl object-cover shadow-sm">
                        @else
                            <div class="flex h-20 w-20 shrink-0 items-center justify-center rounded-2xl bg-[#f1e3d6] font-handwritten text-4xl font-bold text-[#713640] dark:bg-[#493536] dark:text-[#f0d8c1]" aria-hidden="true">
                                {{ strtoupper(substr($profile->name, 0, 1)) }}
                            </div>
                        @endif

                        <div class="min-w-0 flex-1">
                            <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[#9b7466] dark:text-[#c7a491]">Bookworm reader</p>
                            <h2 id="reader-profile-heading" class="mt-1 break-words text-3xl font-semibold leading-tight text-[#392b2a] dark:text-[#f4e9e2]">{{ $profile->name }}</h2>
                            <p class="mt-1 text-sm text-[#8a7065] dark:text-[#c1aaa0]">{{ '@' . $profile->username }}</p>

                            @if ($profile->bio)
                                <p class="mt-4 whitespace-pre-line break-words text-sm leading-6 text-[#564541] dark:text-[#e3d3ca]">{{ $profile->bio }}</p>
                            @else
                                <p class="mt-4 text-sm italic text-[#9a8579] dark:text-[#b39d93]">No introduction yet.</p>
                            @endif

                            <p class="mt-4 text-xs text-[#9a8579] dark:text-[#b39d93]">
                                Reading with Bookworm since {{ $profile->created_at->format('F Y') }}
                            </p>
                        </div>

                        <div class="shrink-0 sm:pt-1">
                            @if ($isOwnProfile)
                                <a href="{{ route('profile.edit') }}"
                                   class="inline-flex w-full items-center justify-center rounded-full border border-[#c9a58f] px-5 py-2.5 text-sm font-semibold text-[#713640] transition hover:bg-[#faf2e9] focus:outline-none focus:ring-2 focus:ring-[#b98768] dark:border-[#765a58] dark:text-[#f0d8c1] dark:hover:bg-[#382c2d] sm:w-auto">
                                    Edit profile and photo
                                </a>
                            @elseif ($isFollowing)
                                <form method="POST" action="{{ route('users.unfollow', $profile->username) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            aria-label="Unfollow {{ $profile->name }}"
                                            class="inline-flex w-full items-center justify-center rounded-full border border-[#c9a58f] px-5 py-2.5 text-sm font-semibold text-[#713640] transition hover:bg-[#faf2e9] focus:outline-none focus:ring-2 focus:ring-[#b98768] dark:border-[#765a58] dark:text-[#f0d8c1] dark:hover:bg-[#382c2d] sm:w-auto">
                                        Following · Unfollow
                                    </button>
                                </form>
                            @else
                                <form method="POST" action="{{ route('users.follow', $profile->username) }}">
                                    @csrf
                                    <button type="submit"
                                            aria-label="Follow {{ $profile->name }}"
                                            class="inline-flex w-full items-center justify-center rounded-full bg-[#5b1f2b] px-6 py-2.5 text-sm font-semibold text-[#fff8ef] shadow-sm transition hover:bg-[#713640] focus:outline-none focus:ring-2 focus:ring-[#b98768] focus:ring-offset-2 dark:focus:ring-offset-[#291f20] sm:w-auto">
                                        Follow reader
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <dl class="mt-7 grid grid-cols-3 overflow-hidden rounded-xl border border-[#eadfd4] bg-[#faf5ef] dark:border-[#453538] dark:bg-[#241b1c]">
                        <div class="px-2 py-4 text-center sm:px-4">
                            <dt class="text-xs text-[#8a7065] dark:text-[#c1aaa0]">Followers</dt>
                            <dd class="mt-1 text-xl font-semibold text-[#392b2a] dark:text-[#f4e9e2]">{{ $profile->followers_count }}</dd>
                        </div>
                        <div class="border-x border-[#eadfd4] px-2 py-4 text-center dark:border-[#453538] sm:px-4">
                            <dt class="text-xs text-[#8a7065] dark:text-[#c1aaa0]">Following</dt>
                            <dd class="mt-1 text-xl font-semibold text-[#392b2a] dark:text-[#f4e9e2]">{{ $profile->following_count }}</dd>
                        </div>
                        <div class="px-2 py-4 text-center sm:px-4">
                            <dt class="text-xs text-[#8a7065] dark:text-[#c1aaa0]">Reviews</dt>
                            <dd class="mt-1 text-xl font-semibold text-[#392b2a] dark:text-[#f4e9e2]">{{ $profile->reviews_count }}</dd>
                        </div>
                    </dl>
                </div>
            </section>

            <section aria-labelledby="reviews-heading">
                <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
                    <div>
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#9b7466] dark:text-[#c7a491]">From the bookshelf</p>
                        <h2 id="reviews-heading" class="mt-1 text-2xl font-semibold text-[#392b2a] dark:text-[#f4e9e2]">Reviews by {{ $profile->name }}</h2>
                    </div>
                    <span class="text-sm text-[#8a7065] dark:text-[#c1aaa0]">
                        {{ $reviews->total() }} {{ Str::plural('review', $reviews->total()) }}
                    </span>
                </div>

                <div class="space-y-4">
                    @forelse ($reviews as $review)
                        <article class="rounded-2xl border border-[#eadfd4] bg-[#fffdf9] p-4 shadow-sm sm:p-5 dark:border-[#453538] dark:bg-[#291f20]">
                            <div class="flex gap-4">
                                @if ($review->book->cover_url)
                                    <img src="{{ $review->book->cover_url }}"
                                         alt="Cover of {{ $review->book->title }}"
                                         loading="lazy"
                                         class="h-28 w-[4.5rem] shrink-0 rounded-lg object-cover shadow-sm sm:h-32 sm:w-20">
                                @else
                                    <div class="flex h-28 w-[4.5rem] shrink-0 items-center justify-center rounded-lg bg-gradient-to-br from-[#f3e6d7] to-[#dfc7b5] p-2 text-center font-handwritten text-sm font-semibold text-[#72584d] dark:from-[#423334] dark:to-[#302526] dark:text-[#ddc9be] sm:h-32 sm:w-20">
                                        {{ $review->book->title }}
                                    </div>
                                @endif

                                <div class="min-w-0 flex-1">
                                    <div class="flex flex-wrap items-start justify-between gap-2">
                                        <a href="{{ route('books.show', $review->book) }}"
                                           class="break-words text-lg font-semibold text-[#392b2a] hover:text-[#713640] focus:outline-none focus:ring-2 focus:ring-[#b98768] dark:text-[#f4e9e2] dark:hover:text-[#e4b3a5]">
                                            {{ $review->book->title }}
                                        </a>
                                        <time datetime="{{ $review->created_at->toIso8601String() }}"
                                              class="shrink-0 text-xs text-[#9a8579] dark:text-[#b39d93]">
                                            {{ $review->created_at->diffForHumans() }}
                                        </time>
                                    </div>

                                    @if ($review->book->author_name)
                                        <p class="mt-1 text-sm text-[#8a7065] dark:text-[#c1aaa0]">{{ $review->book->author_name }}</p>
                                    @endif

                                    <div class="mt-2">
                                        <x-star-display :rating="$review->rating" />
                                    </div>

                                    @if ($review->body)
                                        <p class="mt-3 whitespace-pre-line break-words text-sm leading-6 text-[#564541] dark:text-[#e3d3ca]">{{ $review->body }}</p>
                                    @endif
                                </div>
                            </div>
                        </article>
                    @empty
                        <div class="rounded-2xl border border-dashed border-[#d9c6b6] bg-[#fffdf9] px-5 py-10 text-center dark:border-[#594749] dark:bg-[#291f20]">
                            <p class="font-handwritten text-2xl font-semibold text-[#713640] dark:text-[#f0d8c1]">No reviews on this shelf yet</p>
                            <p class="mt-1 text-sm text-[#8a7065] dark:text-[#c1aaa0]">When {{ $isOwnProfile ? 'you' : $profile->name }} reviews a book, it will show up here.</p>
                            @if ($isOwnProfile)
                                <a href="{{ route('books.index') }}" class="mt-4 inline-flex rounded-full bg-[#5b1f2b] px-5 py-2 text-sm font-semibold text-[#fff8ef] transition hover:bg-[#713640]">
                                    Find a book to review
                                </a>
                            @endif
                        </div>
                    @endforelse
                </div>

                @if ($reviews->hasPages())
                    <div class="mt-6">{{ $reviews->links() }}</div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
