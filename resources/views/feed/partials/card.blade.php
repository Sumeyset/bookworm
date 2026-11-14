{{-- One feed item, Instagram style: a post, a shared quote, or a review. --}}
@php
    $isReview = $item instanceof \App\Models\Review;
    $isQuote = ! $isReview && $item->isQuote();
    $photos = $isReview ? collect() : $item->photos;
    $hasPhotos = $photos->isNotEmpty();
    $hasCard = ! $isReview && ! $hasPhotos && $item->hasCard();
    $type = $item->getMorphClass();
    $key = $type . '-' . $item->id;
    $permalink = $isReview ? route('books.show', $item->book) : route('posts.show', $item);
    $verb = $isReview ? ($item->body ? 'reviewed' : 'rated') : null;
    $caption = $isReview ? $item->body : (($isQuote || $hasPhotos || ! $hasCard) ? $item->body : null);
    $comments = $item->comments;
    $creditMeta = collect([$item->book?->author_name, ! $isReview && $item->quote_page ? 'p. ' . $item->quote_page : null])->filter()->implode(' · ');
@endphp
<article id="{{ $key }}" class="ig-post scroll-mt-28" x-data="{ menu: false, all: false, more: false }">
    {{-- Header --}}
    <header class="ig-post__head">
        <a href="{{ route('users.show', $item->user->username) }}" class="ig-ring focus-ring" aria-label="{{ $item->user->name }}’s profile">
            <x-avatar :src="$item->user->profile_photo_url" :name="$item->user->name" size="sm" />
        </a>
        <div class="min-w-0 flex-1 leading-tight">
            <p class="truncate text-sm">
                <a href="{{ route('users.show', $item->user->username) }}" class="font-semibold text-ink hover:underline dark:text-[#F4E9E2]">{{ $item->user->username ?? $item->user->name }}</a>
                @if ($verb)<span class="text-tan"> {{ $verb }}</span>@endif
                <span class="text-tan"> · <time datetime="{{ $item->created_at->toIso8601String() }}">{{ $item->created_at->diffForHumans(short: true) }}</time></span>
            </p>
            @if ($item->book)
                <a href="{{ route('books.show', $item->book) }}" class="block truncate text-xs text-walnut hover:underline dark:text-[#d4c2b9]">{{ $item->book->title }}</a>
            @endif
        </div>
        <div class="relative">
            <button type="button" @click="menu = !menu" @click.outside="menu = false" class="ig-icon !h-9 !w-9" :aria-expanded="menu" aria-label="More options">
                <svg viewBox="0 0 24 24" aria-hidden="true"><circle cx="5" cy="12" r="1.2" fill="currentColor"/><circle cx="12" cy="12" r="1.2" fill="currentColor"/><circle cx="19" cy="12" r="1.2" fill="currentColor"/></svg>
            </button>
            <div x-show="menu" x-cloak x-transition.origin.top.right class="absolute right-0 z-20 mt-1 w-48 overflow-hidden rounded-2xl border border-warmborder bg-paper py-1 text-sm shadow-xl dark:border-[#4B3035] dark:bg-raised">
                @unless ($isReview)
                    <a href="{{ $permalink }}" class="block px-4 py-2.5 hover:bg-parchment dark:hover:bg-white/5">Go to post</a>
                @endunless
                <button type="button" data-share-url="{{ $permalink }}" data-share-title="{{ $item->book?->title ?? 'Bookworm' }}" @click="menu = false" class="block w-full px-4 py-2.5 text-left hover:bg-parchment dark:hover:bg-white/5">Copy link</button>
                @if (! $isReview && $item->user_id === auth()->id())
                    <form method="POST" action="{{ route('posts.destroy', $item) }}" onsubmit="return confirm('Delete this post?')">
                        @csrf @method('DELETE')
                        <button class="block w-full px-4 py-2.5 text-left font-semibold text-red-700 hover:bg-parchment dark:text-red-300 dark:hover:bg-white/5">Delete</button>
                    </form>
                @endif
            </div>
        </div>
    </header>

    {{-- Media --}}
    @if ($hasPhotos)
        <div class="ig-post__media" data-double-like>
            <div class="ig-carousel ig-ratio--{{ $item->photoRatio() }}" data-carousel>
                <div class="ig-carousel__track" data-carousel-track>
                    @foreach ($photos as $photo)
                        <div class="ig-carousel__slide">
                            <img src="{{ $photo->url() }}" alt="Photo {{ $loop->iteration }} of {{ $photos->count() }} by {{ $item->user->name }}" class="{{ $photo->filterClass() }}" style="object-position: {{ $photo->focus_x }}% {{ $photo->focus_y }}%" @if ($photo->width && $photo->height) width="{{ $photo->width }}" height="{{ $photo->height }}" @endif loading="lazy" decoding="async" draggable="false">
                        </div>
                    @endforeach
                </div>
                @if ($photos->count() > 1)
                    <span class="ig-carousel__count" data-carousel-count>1/{{ $photos->count() }}</span>
                    <button type="button" class="ig-carousel__arrow ig-carousel__arrow--prev" data-carousel-prev hidden aria-label="Previous photo"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg></button>
                    <button type="button" class="ig-carousel__arrow ig-carousel__arrow--next" data-carousel-next aria-label="Next photo"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M9 6l6 6-6 6"/></svg></button>
                @endif
            </div>
            <svg class="ig-heart-burst" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 21s-7.8-4.8-9.7-9.6C.9 7.9 3 4 6.9 4c2.1 0 3.8 1.2 5.1 3.1C13.3 5.2 15 4 17.1 4 21 4 23.1 7.9 21.7 11.4 19.8 16.2 12 21 12 21Z"/></svg>
        </div>
    @elseif ($hasCard)
        <div class="ig-post__media" data-double-like>
            <x-quote-card
                :text="$isQuote ? $item->quote_text : $item->body"
                :design="$item->cardStyle()"
                :font="$item->cardFont()"
                :title="$isQuote ? ($item->book?->title ?? null) : null"
                :meta="$isQuote ? $creditMeta : null"
                :thought="! $isQuote" />
            <svg class="ig-heart-burst" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 21s-7.8-4.8-9.7-9.6C.9 7.9 3 4 6.9 4c2.1 0 3.8 1.2 5.1 3.1C13.3 5.2 15 4 17.1 4 21 4 23.1 7.9 21.7 11.4 19.8 16.2 12 21 12 21Z"/></svg>
        </div>
    @elseif ($isReview)
        <div class="ig-post__media relative flex aspect-[4/3] items-center justify-center overflow-hidden bg-[#2a1a1d]" data-double-like>
            @if ($item->book->cover_url)
                <img src="{{ $item->book->cover_url }}" alt="" class="absolute inset-0 h-full w-full scale-110 object-cover opacity-50 blur-2xl" loading="lazy" aria-hidden="true">
            @endif
            <div class="relative flex flex-col items-center gap-3 py-6">
                <x-book-cover :book="$item->book" class="w-28 shadow-2xl sm:w-32" />
                <span class="rounded-full bg-black/35 px-3 py-1 backdrop-blur"><x-star-display :rating="$item->rating" /></span>
            </div>
            <svg class="ig-heart-burst" viewBox="0 0 24 24" aria-hidden="true"><path fill="currentColor" d="M12 21s-7.8-4.8-9.7-9.6C.9 7.9 3 4 6.9 4c2.1 0 3.8 1.2 5.1 3.1C13.3 5.2 15 4 17.1 4 21 4 23.1 7.9 21.7 11.4 19.8 16.2 12 21 12 21Z"/></svg>
        </div>
    @endif

    {{-- Actions --}}
    <div class="ig-actions {{ $photos->count() > 1 ? 'relative' : '' }}">
        @if ($photos->count() > 1)
            <div class="ig-dots pointer-events-none absolute inset-x-0 top-0" data-carousel-dots aria-hidden="true">
                @foreach ($photos as $photo)<span @class(['is-active' => $loop->first])></span>@endforeach
            </div>
        @endif
        <button type="button" class="ig-icon" data-like-url="{{ route('reactions.like', [$type, $item->id]) }}" data-like-key="{{ $key }}" aria-pressed="{{ $item->liked_by_viewer ? 'true' : 'false' }}" aria-label="Like">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20.5s-7.5-4.6-9.3-9.2C1.4 8 3.4 4.5 7 4.5c2 0 3.6 1.1 5 3 1.4-1.9 3-3 5-3 3.6 0 5.6 3.5 4.3 6.8-1.8 4.6-9.3 9.2-9.3 9.2Z"/></svg>
        </button>
        <button type="button" class="ig-icon" @click="all = true; $nextTick(() => $refs.reply.focus())" aria-label="Reply">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M20.5 11.6a8.4 8.4 0 0 1-12.3 7.5L3.5 20.5l1.4-4.6A8.4 8.4 0 1 1 20.5 11.6Z"/></svg>
        </button>
        <button type="button" class="ig-icon" data-share-url="{{ $permalink }}" data-share-title="{{ $item->book?->title ?? 'Bookworm' }}" @unless ($isReview) data-send-post="{{ route('posts.send', $item) }}" @endunless aria-label="Send">
            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M21.5 3.5 10.6 13.4M21.5 3.5 14.6 21l-4-7.6-7.6-4 18.5-5.9Z"/></svg>
        </button>
        @if ($item->book)
            <a href="{{ route('books.show', $item->book) }}" class="ig-icon ml-auto" aria-label="Open {{ $item->book->title }}">
                <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M4 5.5A2.5 2.5 0 0 1 6.5 3H20v15H6.5A2.5 2.5 0 0 0 4 20.5v-15ZM4 20.5A2.5 2.5 0 0 0 6.5 23H20v-5"/></svg>
            </a>
        @endif
    </div>

    <div class="ig-body text-ink dark:text-[#F4E9E2]">
        <p class="ig-likes"><span data-like-total="{{ $key }}" data-count="{{ $item->likes_count }}">{{ $item->likes_count }} {{ Str::plural('like', $item->likes_count) }}</span></p>

        @if (! $isReview && ! $hasCard && ! $hasPhotos)
            {{-- A plain thought: the words are the post. --}}
            <p class="mt-1 whitespace-pre-line break-words text-[17px] leading-7">{{ $item->body }}</p>
        @elseif ($caption)
            <p class="ig-caption" :class="more ? '' : 'is-clamped'"><a href="{{ route('users.show', $item->user->username) }}" class="font-semibold hover:underline">{{ $item->user->username ?? $item->user->name }}</a>@if ($isReview) <span class="font-semibold">{{ $item->book->title }} ·</span>@endif {{ $caption }}</p>
            @if (mb_strlen($caption) > 160)
                <button type="button" x-show="!more" @click="more = true" class="text-sm text-tan hover:underline">more</button>
            @endif
        @endif

        <p class="ig-time"><time datetime="{{ $item->created_at->toIso8601String() }}">{{ $item->created_at->diffForHumans() }}</time></p>
    </div>

    {{-- Replies: their own section, separate from the caption --}}
    <section class="ig-replies" aria-label="Replies">
        <div class="flex items-center justify-between">
            <h3 class="ig-replies__title">
                Replies
                @if ($comments->isNotEmpty())<span class="ig-replies__count">{{ $comments->count() }}</span>@endif
            </h3>
            @if ($comments->count() > 2)
                <button type="button" @click="all = !all" class="text-xs font-semibold text-tan hover:text-burgundy hover:underline dark:hover:text-[#E9A2AE]" x-text="all ? 'Show fewer' : 'View all {{ $comments->count() }}'">View all {{ $comments->count() }}</button>
            @endif
        </div>

        @if ($comments->isEmpty())
            <p class="mt-1 text-sm text-tan">No replies yet. Start the conversation.</p>
        @else
            <ul class="mt-2 space-y-3">
                @foreach ($comments as $index => $comment)
                    <li class="group flex gap-2.5" @if ($index < $comments->count() - 2) x-show="all" x-cloak @endif>
                        <a href="{{ route('users.show', $comment->user->username) }}" class="shrink-0" tabindex="-1" aria-hidden="true">
                            <x-avatar :src="$comment->user->profile_photo_url" :name="$comment->user->name" size="sm" />
                        </a>
                        <div class="min-w-0 flex-1">
                            <div class="text-xs">
                                <a href="{{ route('users.show', $comment->user->username) }}" class="font-semibold text-ink hover:underline dark:text-[#F4E9E2]">{{ $comment->user->username ?? $comment->user->name }}</a>
                                <span class="text-tan">· {{ $comment->created_at->diffForHumans(short: true) }}</span>
                                @if ($comment->user_id === auth()->id())
                                    <form method="POST" action="{{ route('comments.destroy', $comment) }}" class="inline opacity-0 transition group-hover:opacity-100 focus-within:opacity-100">
                                        @csrf @method('DELETE')
                                        <button class="ml-1 text-tan hover:text-burgundy" aria-label="Delete reply">Delete</button>
                                    </form>
                                @endif
                            </div>
                            <p class="mt-0.5 whitespace-pre-line break-words text-sm leading-6 text-ink dark:text-[#F4E9E2]">{{ $comment->body }}</p>
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

        <form method="POST" action="{{ route('reactions.comment', [$type, $item->id]) }}" class="ig-replies__form">
            @csrf
            <x-avatar :src="auth()->user()->profile_photo_url" :name="auth()->user()->name" size="sm" />
            <label class="sr-only" for="reply-{{ $key }}">Add a reply</label>
            <input x-ref="reply" id="reply-{{ $key }}" name="body" maxlength="1000" required autocomplete="off" placeholder="Add a reply…" class="min-w-0 flex-1 border-0 bg-transparent px-0 py-1.5 text-sm placeholder:text-tan/70 focus:ring-0">
            <button type="submit" class="text-sm font-semibold text-burgundy hover:text-burgundy-hover dark:text-[#E9A2AE]">Post</button>
        </form>
    </section>
</article>
