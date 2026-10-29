<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#9b7466] dark:text-[#c7a491]">Bookworm community</p>
                <h1 class="mt-1 text-2xl font-semibold text-[#392b2a] dark:text-[#f4e9e2]">{{ $isOwnProfile ? 'My Journal' : 'Reader profile' }}</h1>
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
                        <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#9b7466] dark:text-[#c7a491]">Bookshelf</p>
                        <h2 id="reviews-heading" class="mt-1 text-2xl font-semibold text-[#392b2a] dark:text-[#f4e9e2]">
                            {{ $isOwnProfile ? "Books I've read & rated" : "Books read & rated by " . $profile->name }}
                        </h2>
                    </div>
                    <span class="text-sm text-[#8a7065] dark:text-[#c1aaa0]">
                        {{ $reviews->total() }} {{ Str::plural('book', $reviews->total()) }}
                    </span>
                </div>

                <div class="overflow-hidden rounded-xl border-[10px] border-[#4c3025] bg-[#2c1d19] shadow-[0_24px_50px_-28px_rgba(48,25,20,0.8),inset_0_0_0_1px_rgba(242,207,166,0.35)] sm:border-[14px]">
                    <div class="relative flex items-center justify-center border-b-4 border-[#38231c] bg-gradient-to-b from-[#845a40] to-[#63412f] px-4 py-5 shadow-[0_5px_12px_rgba(31,17,13,0.5)]">
                        <span class="absolute inset-x-0 top-1/2 h-px bg-[#e1bb8c]/25" aria-hidden="true"></span>
                        <div class="relative rounded border border-[#dbb37f]/70 bg-[#51362a] px-6 py-2 text-center shadow-[inset_0_1px_3px_rgba(20,12,9,0.7),0_2px_4px_rgba(25,13,10,0.35)] sm:px-9">
                            <p class="text-[10px] font-semibold uppercase tracking-[0.3em] text-[#e5c69f]">The reading room</p>
                            <p class="mt-0.5 font-serif text-sm italic text-[#fff4e6]">Stories worth keeping</p>
                        </div>
                    </div>

                    <div class="relative bg-[linear-gradient(90deg,rgba(35,19,15,0.75),transparent_8%,rgba(255,221,176,0.08)_50%,transparent_92%,rgba(35,19,15,0.75)),repeating-linear-gradient(176deg,#654436_0px,#704b3c_4px,#5e3d31_8px,#704b3c_13px)] before:absolute before:inset-y-0 before:left-0 before:w-2 before:bg-gradient-to-r before:from-[#39251e] before:to-[#966747]/50 after:absolute after:inset-y-0 after:right-0 after:w-2 after:bg-gradient-to-l after:from-[#39251e] after:to-[#966747]/50">
                        @forelse ($reviews->getCollection()->chunk(5) as $shelf)
                            <div class="relative">
                            <div role="region" aria-label="Bookshelf row" tabindex="0" class="flex min-h-64 items-end justify-start gap-1 overflow-x-auto overscroll-x-contain px-4 pt-6 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[#fff0d9] sm:justify-center sm:gap-2 sm:px-6">
                                @foreach ($shelf as $review)
                                    <article class="flex h-full flex-none items-end">
                                        <a href="{{ route('books.show', $review->book) }}"
                                           aria-label="{{ $review->book->title }} by {{ $review->book->author_name ?? 'Unknown author' }}, rated {{ number_format($review->rating, 1) }} out of 5"
                                           title="{{ $review->book->title }} · {{ number_format($review->rating, 1) }} out of 5"
                                           class="group/book relative flex {{ $loop->iteration % 4 === 0 ? 'h-40 w-12 sm:h-44 sm:w-14' : ($loop->iteration % 3 === 0 ? 'h-48 w-14 sm:h-52 sm:w-16' : ($loop->iteration % 2 === 0 ? 'h-44 w-12 sm:h-48 sm:w-14' : 'h-52 w-14 sm:h-56 sm:w-16')) }} {{ $loop->iteration % 5 === 0 ? 'bg-gradient-to-r from-[#333044] via-[#66577e] to-[#3e354e]' : ($loop->iteration % 5 === 1 ? 'bg-gradient-to-r from-[#34201f] via-[#874344] to-[#4b2929]' : ($loop->iteration % 5 === 2 ? 'bg-gradient-to-r from-[#303927] via-[#65744b] to-[#3c482f]' : ($loop->iteration % 5 === 3 ? 'bg-gradient-to-r from-[#263a37] via-[#4d7669] to-[#29453d]' : 'bg-gradient-to-r from-[#493628] via-[#a36e43] to-[#63442d]'))) }} items-center justify-center overflow-hidden rounded-t-[3px] border-l-[5px] border-[#f5dfbd]/70 shadow-[4px_1px_5px_rgba(22,12,9,0.72),inset_-4px_0_6px_rgba(28,15,12,0.4)] transition duration-200 hover:-translate-y-2 hover:brightness-125 focus-visible:z-10 focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#fff0d9]">
                                            <span class="absolute inset-y-0 left-[5px] w-[3px] bg-[#f7e5c9]/20" aria-hidden="true"></span>
                                            <span class="absolute inset-x-1 top-2 h-1 border-y border-[#efc783]/70 bg-[#291b1a]/25" aria-hidden="true"></span>
                                            <span class="absolute inset-x-1 bottom-2 h-1 border-y border-[#efc783]/70 bg-[#291b1a]/25" aria-hidden="true"></span>
                                            <span class="max-h-[78%] overflow-hidden px-1 font-serif text-[10px] font-semibold leading-tight tracking-wide text-[#fff0d9] [writing-mode:vertical-rl] [transform:rotate(180deg)]">
                                                {{ $review->book->title }}
                                            </span>
                                            <span class="absolute inset-y-0 right-0 w-1 bg-gradient-to-l from-black/25 to-transparent" aria-hidden="true"></span>
                                        </a>
                                    </article>
                                @endforeach
                            </div>
                            <div class="relative z-10 h-4 border-y border-[#e9c18e]/80 bg-gradient-to-b from-[#e0b77f] via-[#b77b4c] to-[#74472f] shadow-[0_7px_12px_rgba(27,14,11,0.7)]">
                                <span class="absolute inset-x-0 top-0 h-px bg-[#fff0d9]/60" aria-hidden="true"></span>
                                <span class="absolute inset-x-0 bottom-0 h-px bg-[#3d251c]/70" aria-hidden="true"></span>
                            </div>
                            </div>
                            <div class="grid gap-px bg-[#3d2922]/80 sm:grid-cols-2 lg:grid-cols-5">
                            @foreach ($shelf as $review)
                                <article class="min-h-28 border-r border-[#d4a674]/15 bg-[#432d25]/90 px-3 py-3 text-[#fff8ef] last:border-0">
                                    <a href="{{ route('books.show', $review->book) }}"
                                       class="line-clamp-1 text-sm font-semibold text-[#fff8ef] hover:text-[#f0d8c1] focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#f0d8c1]">
                                        {{ $review->book->title }}
                                    </a>
                                    @if ($review->book->author_name)
                                        <p class="mt-1 line-clamp-1 text-xs text-[#d9bd9e]">{{ $review->book->author_name }}</p>
                                    @endif
                                    <div class="mt-2"><x-star-display :rating="$review->rating" /></div>
                                    @if ($review->body)
                                        <p class="mt-2 line-clamp-2 break-words text-xs leading-5 text-[#eadbd2]">{{ $review->body }}</p>
                                    @endif
                                </article>
                            @endforeach
                            </div>
                        @empty
                            <div class="m-4 rounded-xl border border-dashed border-[#d6a168]/70 bg-[#543936]/90 px-5 py-12 text-center text-[#fff8ef] sm:m-6">
                            <p class="font-handwritten text-3xl font-semibold text-[#f0d8c1]">A little room for your first story</p>
                            <p class="mt-2 text-sm text-[#e2cabb]">Rate a book and it will find a place on your shelf.</p>
                            @if ($isOwnProfile)
                                <a href="{{ route('books.index') }}" class="mt-5 inline-flex rounded-full bg-[#e3bd82] px-5 py-2.5 text-sm font-semibold text-[#392b2a] shadow-sm transition hover:bg-[#f0d8c1]">
                                    Find a book to review
                                </a>
                            @endif
                            </div>
                        @endforelse
                    </div>
                    <div class="h-4 border-t border-[#e7be8c]/70 bg-gradient-to-b from-[#d3a06b] via-[#a86e45] to-[#71472f] shadow-[0_-4px_10px_rgba(27,14,11,0.65)]"></div>

                @if ($reviews->hasPages())
                    <div class="mt-6">{{ $reviews->links() }}</div>
                @endif
            </section>
        </div>
    </div>
</x-app-layout>
