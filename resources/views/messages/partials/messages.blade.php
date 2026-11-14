{{-- Chat bubbles. Expects $messages (oldest first) and $viewer. --}}
@foreach ($messages as $message)
    @php $mine = $message->user_id === $viewer->id; @endphp
    <div class="dm-row {{ $mine ? 'dm-row--mine' : '' }}" data-message-id="{{ $message->id }}" data-sender="{{ $message->user_id }}" data-time="{{ $message->created_at->toIso8601String() }}">
        @unless ($mine)
            <x-avatar :src="$message->user->profile_photo_url" :name="$message->user->name" size="sm" class="dm-row__avatar !h-7 !w-7 !text-xs !ring-0 !ring-offset-0" />
        @endunless
        <div class="dm-row__stack">
            @if ($message->post_id)
                @php $post = $message->post; @endphp
                @if ($post)
                    @php $photo = $post->photos->first(); @endphp
                    <a href="{{ route('posts.show', $post) }}" class="dm-post">
                        <span class="dm-post__head">
                            <x-avatar :src="$post->user->profile_photo_url" :name="$post->user->name" size="sm" class="!h-6 !w-6 !text-[10px] !ring-0 !ring-offset-0" />
                            <b>{{ $post->user->username }}</b>
                        </span>
                        @if ($photo)
                            <img src="{{ $photo->url() }}" alt="" class="dm-post__media {{ $photo->filterClass() }}" style="object-position: {{ $photo->focus_x }}% {{ $photo->focus_y }}%" loading="lazy">
                        @elseif ($post->hasCard())
                            <span class="dm-post__media dm-post__card"><x-quote-card :text="$post->isQuote() ? $post->quote_text : $post->body" :design="$post->cardStyle()" :font="$post->cardFont()" :title="$post->isQuote() ? $post->book?->title : null" :thought="! $post->isQuote()" /></span>
                        @endif
                        @if ($post->body && ($photo || $post->isQuote() || ! $post->hasCard()))
                            <span class="dm-post__caption"><b>{{ $post->user->username }}</b> {{ Str::limit($post->body, 90) }}</span>
                        @endif
                    </a>
                @else
                    <p class="dm-bubble dm-bubble--muted">This post was deleted.</p>
                @endif
            @endif
            @if (filled($message->body))
                <p class="dm-bubble" title="{{ $message->created_at->format('j M Y, H:i') }}">{{ $message->body }}</p>
            @endif
        </div>
    </div>
@endforeach
