{{-- Instagram-style grid of a reader's posts: photos, quote cards and thoughts. Expects $posts and $profile. --}}
<section id="posts" aria-labelledby="posts-heading" class="scroll-mt-24">
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="eyebrow">{{ $isOwnProfile ? 'Everything you’ve shared' : 'Shared by ' . $profile->name }}</p>
            <h2 id="posts-heading" class="font-display mt-1 text-4xl font-bold text-ink dark:text-[#f4e9e2]">Posts</h2>
        </div>
        @if ($isOwnProfile)
            <a href="{{ route('feed.index', ['compose' => 'photo']) }}" class="btn-primary">+ New post</a>
        @endif
    </div>

    @if ($posts->isEmpty())
        <div class="bw-card flex flex-col items-center gap-3 px-6 py-12 text-center">
            <span class="flex h-16 w-16 items-center justify-center rounded-full border-2 border-ink/80 dark:border-[#f4e9e2]/80" aria-hidden="true">
                <svg class="h-8 w-8 text-ink dark:text-[#f4e9e2]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="5" width="18" height="15" rx="3"/><circle cx="12" cy="12.5" r="3.5"/><path d="M8 5l1.5-2h5L16 5"/></svg>
            </span>
            <p class="font-serif text-2xl font-semibold text-ink dark:text-[#f4e9e2]">{{ $isOwnProfile ? 'Share your first post' : 'No posts yet' }}</p>
            <p class="max-w-sm text-sm text-tan">{{ $isOwnProfile ? 'Photos of your shelf, a quote that stayed with you, a thought about what you’re reading — they’ll all show up here.' : 'When ' . $profile->name . ' shares photos, quotes or thoughts, they’ll appear here.' }}</p>
            @if ($isOwnProfile)
                <a href="{{ route('feed.index', ['compose' => 'photo']) }}" class="btn-ghost mt-1">Share a photo</a>
            @endif
        </div>
    @else
        <ul class="grid grid-cols-3 gap-0.5 overflow-hidden rounded-2xl sm:gap-1">
            @foreach ($posts as $post)
                @php
                    $firstPhoto = $post->photos->first();
                    $label = $post->isQuote() ? 'Quote' : ($firstPhoto ? 'Photo post' : 'Thought');
                @endphp
                <li>
                    <a href="{{ route('posts.show', $post) }}" class="ig-tile group" aria-label="{{ $label }} · {{ $post->likes_count }} {{ Str::plural('like', $post->likes_count) }}, {{ $post->comments_count }} {{ Str::plural('reply', $post->comments_count) }}">
                        @if ($firstPhoto)
                            <img src="{{ $firstPhoto->url() }}" alt="" class="{{ $firstPhoto->filterClass() }}" style="object-position: {{ $firstPhoto->focus_x }}% {{ $firstPhoto->focus_y }}%" loading="lazy" decoding="async">
                        @elseif ($post->hasCard())
                            <x-quote-card :text="$post->isQuote() ? $post->quote_text : $post->body" :design="$post->cardStyle()" :font="$post->cardFont()" :title="$post->isQuote() ? $post->book?->title : null" :thought="! $post->isQuote()" />
                        @else
                            <span class="ig-tile__text">{{ $post->body }}</span>
                        @endif

                        @if ($post->photos->count() > 1)
                            <svg class="ig-tile__badge" viewBox="0 0 24 24" aria-hidden="true"><rect x="7" y="7" width="13" height="13" rx="2.5" fill="currentColor"/><path d="M4 16V6.5A2.5 2.5 0 0 1 6.5 4H16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"/></svg>
                        @elseif ($post->isQuote())
                            <span class="ig-tile__badge ig-tile__badge--quote" aria-hidden="true">“</span>
                        @endif

                        <span class="ig-tile__overlay" aria-hidden="true">
                            <span><svg viewBox="0 0 24 24"><path fill="currentColor" d="M12 21s-7.8-4.8-9.7-9.6C.9 7.9 3 4 6.9 4c2.1 0 3.8 1.2 5.1 3.1C13.3 5.2 15 4 17.1 4 21 4 23.1 7.9 21.7 11.4 19.8 16.2 12 21 12 21Z"/></svg>{{ $post->likes_count }}</span>
                            <span><svg viewBox="0 0 24 24"><path fill="currentColor" d="M20.5 11.6a8.4 8.4 0 0 1-12.3 7.5L3.5 20.5l1.4-4.6A8.4 8.4 0 1 1 20.5 11.6Z"/></svg>{{ $post->comments_count }}</span>
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
        @if ($posts->hasPages())
            <div class="mt-5">{{ $posts->links() }}</div>
        @endif
    @endif
</section>
