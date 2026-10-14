<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">{{ $book->title }}</h2>
            <a href="{{ route('books.index') }}" class="text-sm font-medium text-[#713640] hover:underline dark:text-[#e4b3a5]">
                &larr; Search books
            </a>
        </div>
    </x-slot>

    <div class="py-8 sm:py-10">
        <div class="mx-auto max-w-4xl space-y-8 px-4">
            @if (session('status'))
                <div class="rounded-xl border border-green-200 bg-green-50 px-4 py-3 text-sm text-green-800 dark:border-green-900 dark:bg-green-950 dark:text-green-200" role="status">
                    {{ session('status') }}
                </div>
            @endif

            <section class="flex flex-col gap-6 rounded-2xl border border-[#eadfd4] bg-[#fffdf9] p-5 shadow-sm sm:flex-row sm:p-7 dark:border-[#453538] dark:bg-[#291f20]">
                @if ($book->cover_url)
                    <img src="{{ $book->cover_url }}" alt="Cover of {{ $book->title }}" class="mx-auto w-36 shrink-0 self-start rounded-xl object-cover shadow-md sm:mx-0">
                @else
                    <div class="flex aspect-[2/3] w-36 shrink-0 items-center justify-center self-start rounded-xl bg-gradient-to-br from-[#f3e6d7] to-[#dfc7b5] p-4 text-center font-handwritten text-xl font-semibold text-[#72584d] dark:from-[#423334] dark:to-[#302526] dark:text-[#ddc9be]">
                        {{ $book->title }}
                    </div>
                @endif

                <div class="flex-1 self-center text-center sm:text-left">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#9b7466] dark:text-[#c7a491]">Book details</p>
                    <h1 class="mt-2 text-3xl font-semibold leading-tight text-[#392b2a] dark:text-[#f4e9e2]">{{ $book->title }}</h1>
                    <p class="mt-2 text-sm text-[#8a7065] dark:text-[#c1aaa0]">
                        {{ $book->author_name ?? 'Unknown author' }}
                        @if ($book->first_publish_year)
                            <span aria-hidden="true">&middot;</span> {{ $book->first_publish_year }}
                        @endif
                    </p>
                    <div class="mt-5 inline-flex items-center gap-2 rounded-full bg-[#faf2e9] px-3 py-2 dark:bg-[#382c2d]">
                        @if ($count)
                            <x-star-display :rating="$average" />
                            <span class="text-sm font-semibold text-[#392b2a] dark:text-[#f4e9e2]">{{ number_format($average, 1) }}</span>
                            <span class="text-xs text-[#8a7065] dark:text-[#c1aaa0]">{{ $count }} {{ Str::plural('review', $count) }}</span>
                        @else
                            <span class="text-sm text-[#8a7065] dark:text-[#c1aaa0]">No reviews yet</span>
                        @endif
                    </div>
                </div>
            </section>

            <section class="rounded-2xl border border-[#eadfd4] bg-[#fffdf9] p-5 shadow-sm sm:p-6 dark:border-[#453538] dark:bg-[#291f20]">
                <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#9b7466] dark:text-[#c7a491]">Your review</p>
                <h2 class="mt-1 text-xl font-semibold text-[#392b2a] dark:text-[#f4e9e2]">
                    {{ $myReview ? 'Edit your review' : 'Rate this book' }}
                </h2>
                <form method="POST" action="{{ route('reviews.store', $book) }}" class="mt-4 space-y-4">
                    @csrf
                    <div>
                        <x-input-label value="Your rating" />
                        <div class="mt-2"><x-star-input :value="old('rating', $myReview->rating ?? 0)" /></div>
                        <x-input-error :messages="$errors->get('rating')" class="mt-2" />
                    </div>
                    <div>
                        <x-input-label for="review-body" value="Your thoughts (optional)" />
                        <textarea id="review-body" name="body" rows="4" maxlength="3000"
                                  class="mt-1 block w-full rounded-xl border-[#dfd2c6] bg-[#fffdf9] text-sm text-[#392b2a] shadow-sm focus:border-[#9b5b54] focus:ring-[#9b5b54] dark:border-[#594749] dark:bg-[#20191a] dark:text-[#f4e9e2]">{{ old('body', $myReview->body ?? '') }}</textarea>
                        <x-input-error :messages="$errors->get('body')" class="mt-2" />
                    </div>
                    <x-primary-button>{{ $myReview ? 'Update review' : 'Save review' }}</x-primary-button>
                </form>
                @if ($myReview)
                    <form method="POST" action="{{ route('reviews.destroy', $myReview) }}" class="mt-3" onsubmit="return confirm('Delete your review?')">
                        @csrf
                        @method('DELETE')
                        <button class="text-sm text-red-600 underline">Delete my review</button>
                    </form>
                @endif
            </section>

            <section class="rounded-2xl border border-[#eadfd4] bg-[#fffdf9] p-5 shadow-sm sm:p-6 dark:border-[#453538] dark:bg-[#291f20]">
                <div class="flex items-baseline justify-between gap-3">
                    <h2 class="text-lg font-semibold text-[#392b2a] dark:text-[#f4e9e2]">Reader reviews</h2>
                    <span class="text-sm text-[#8a7065] dark:text-[#c1aaa0]">{{ $count }} {{ Str::plural('review', $count) }}</span>
                </div>
                <div class="mt-4 divide-y divide-[#f0e8df] dark:divide-[#453538]">
                    @forelse ($reviews as $review)
                        <article class="py-4 first:pt-0 last:pb-0">
                            <div class="flex flex-wrap items-center gap-2">
                                <span class="text-sm font-semibold text-[#392b2a] dark:text-[#f4e9e2]">{{ '@' . $review->user->username }}</span>
                                <x-star-display :rating="$review->rating" />
                                <span class="text-xs text-[#9a8579] dark:text-[#b39d93]">{{ $review->created_at->diffForHumans() }}</span>
                            </div>
                            @if ($review->body)
                                <p class="mt-2 whitespace-pre-line break-words text-sm leading-6 text-[#564541] dark:text-[#e3d3ca]">{{ $review->body }}</p>
                            @endif
                        </article>
                    @empty
                        <p class="py-5 text-sm text-[#8a7065] dark:text-[#c1aaa0]">Be the first to review this book.</p>
                    @endforelse
                </div>
                @if ($reviews->hasPages())
                    <div class="mt-4 border-t border-[#f0e8df] pt-4 dark:border-[#453538]">{{ $reviews->links() }}</div>
                @endif
            </section>

            <section class="overflow-hidden rounded-2xl border border-[#eadfd4] bg-[#fffdf9] shadow-sm dark:border-[#453538] dark:bg-[#291f20]">
                <div class="border-b border-[#f0e8df] px-5 py-5 sm:px-6 dark:border-[#453538]">
                    <p class="text-xs font-semibold uppercase tracking-[0.18em] text-[#9b7466] dark:text-[#c7a491]">Join the conversation</p>
                    <h2 class="mt-1 text-xl font-semibold text-[#392b2a] dark:text-[#f4e9e2]">Leave a comment</h2>
                    <form method="POST" action="{{ route('books.comments.store', $book) }}" class="mt-4">
                        @csrf
                        <label for="comment-body" class="sr-only">Your comment</label>
                        <textarea id="comment-body" name="body" rows="3" maxlength="3000" required
                                  placeholder="What would you like to discuss?"
                                  class="block w-full rounded-xl border-[#dfd2c6] bg-[#fffdf9] text-sm text-[#392b2a] shadow-sm placeholder:text-[#a99487] focus:border-[#9b5b54] focus:ring-[#9b5b54] dark:border-[#594749] dark:bg-[#20191a] dark:text-[#f4e9e2]">{{ old('body', $myComment->body ?? '') }}</textarea>
                        <x-input-error :messages="$errors->get('body')" class="mt-2" />
                        <div class="mt-3 flex flex-wrap items-center justify-between gap-3">
                            <p class="text-xs text-[#8a7065] dark:text-[#c1aaa0]">Be kind and keep it relevant to the book.</p>
                            <x-primary-button>{{ $myComment ? 'Update comment' : 'Post comment' }}</x-primary-button>
                        </div>
                    </form>
                </div>

                <div class="px-5 py-5 sm:px-6">
                    <div class="flex items-baseline justify-between gap-3">
                        <h2 class="text-lg font-semibold text-[#392b2a] dark:text-[#f4e9e2]">Comments</h2>
                        <span class="text-sm text-[#8a7065] dark:text-[#c1aaa0]">{{ $comments->total() }} {{ Str::plural('comment', $comments->total()) }}</span>
                    </div>
                    <div class="mt-4 divide-y divide-[#f0e8df] dark:divide-[#453538]">
                        @forelse ($comments as $comment)
                            <article id="comment-{{ $comment->id }}" class="scroll-mt-24 py-4 first:pt-0 last:pb-0">
                                <div class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2">
                                    <div class="flex items-center gap-2">
                                        <span class="flex h-8 w-8 items-center justify-center rounded-full bg-[#f1e3d6] text-xs font-semibold text-[#713640] dark:bg-[#493536] dark:text-[#f0d8c1]">{{ strtoupper(substr($comment->user->username, 0, 1)) }}</span>
                                        <div>
                                            <p class="text-sm font-semibold text-[#392b2a] dark:text-[#f4e9e2]">{{ '@' . $comment->user->username }}</p>
                                            <p class="text-xs text-[#9a8579] dark:text-[#b39d93]">{{ $comment->created_at->diffForHumans() }}</p>
                                        </div>
                                    </div>
                                    @if ($comment->user_id === auth()->id())
                                        <form method="POST" action="{{ route('books.comments.destroy', [$book, $comment]) }}" onsubmit="return confirm('Delete your comment?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-medium text-[#9b5b54] hover:underline">Delete</button>
                                        </form>
                                    @endif
                                </div>
                                <p class="mt-3 whitespace-pre-line break-words text-sm leading-6 text-[#564541] dark:text-[#e3d3ca]">{{ $comment->body }}</p>
                                <div class="mt-3 flex items-center gap-2">
                                    <form method="POST" action="{{ route('books.comments.vote', [$book, $comment]) }}">
                                        @csrf<input type="hidden" name="value" value="1">
                                        <button type="submit" aria-label="Like comment" aria-pressed="{{ $comment->votes->first()?->value === 1 ? 'true' : 'false' }}"
                                                class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-xs font-medium {{ $comment->votes->first()?->value === 1 ? 'border-green-300 bg-green-50 text-green-800 dark:border-green-800 dark:bg-green-950 dark:text-green-200' : 'border-[#eadfd4] text-[#72584d] dark:border-[#594749] dark:text-[#ddc9be]' }}">
                                            <span aria-hidden="true">👍</span>{{ $comment->likes_count }}
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('books.comments.vote', [$book, $comment]) }}">
                                        @csrf<input type="hidden" name="value" value="-1">
                                        <button type="submit" aria-label="Dislike comment" aria-pressed="{{ $comment->votes->first()?->value === -1 ? 'true' : 'false' }}"
                                                class="inline-flex items-center gap-1.5 rounded-lg border px-2.5 py-1.5 text-xs font-medium {{ $comment->votes->first()?->value === -1 ? 'border-red-300 bg-red-50 text-red-800 dark:border-red-900 dark:bg-red-950 dark:text-red-200' : 'border-[#eadfd4] text-[#72584d] dark:border-[#594749] dark:text-[#ddc9be]' }}">
                                            <span aria-hidden="true">👎</span>{{ $comment->dislikes_count }}
                                        </button>
                                    </form>
                                </div>
                            </article>
                        @empty
                            <div class="rounded-xl bg-[#faf5ef] px-4 py-8 text-center dark:bg-[#241b1c]">
                                <p class="font-handwritten text-2xl font-semibold text-[#72584d] dark:text-[#ddc9be]">Start the conversation</p>
                                <p class="mt-1 text-sm text-[#8a7065] dark:text-[#c1aaa0]">Share a thought about this book with the community.</p>
                            </div>
                        @endforelse
                    </div>
                    @if ($comments->hasPages())
                        <div class="mt-6 border-t border-[#f0e8df] pt-4 dark:border-[#453538]">{{ $comments->links() }}</div>
                    @endif
                </div>
            </section>
        </div>
    </div>
</x-app-layout>
