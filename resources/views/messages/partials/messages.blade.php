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
            @php
                $images = $message->attachments->where('type', 'image')->values();
                $audio = $message->attachments->firstWhere('type', 'audio');
                $files = $message->attachments->where('type', 'file');
            @endphp
            @if ($images->isNotEmpty())
                <div class="dm-photos dm-photos--{{ min($images->count(), 4) }}">
                    @foreach ($images->take(4) as $image)
                        <button type="button" class="dm-photos__item" data-dm-view="{{ $image->url() }}" data-dm-gallery="{{ $images->map->url()->implode('|') }}" data-dm-index="{{ $loop->index }}" aria-label="Open photo {{ $loop->iteration }} of {{ $images->count() }}">
                            <img src="{{ $image->url() }}" alt="" loading="lazy" decoding="async" @if ($images->count() === 1 && $image->width && $image->height) width="{{ $image->width }}" height="{{ $image->height }}" @endif>
                            @if ($loop->last && $images->count() > 4)<span class="dm-photos__more">+{{ $images->count() - 4 }}</span>@endif
                        </button>
                    @endforeach
                </div>
            @endif
            @if ($audio)
                <div class="dm-voice" data-dm-voice data-src="{{ $audio->url() }}" data-duration="{{ $audio->duration }}">
                    <button type="button" class="dm-voice__play" data-dm-voice-play aria-label="Play voice message">
                        <svg class="dm-voice__icon-play" viewBox="0 0 24 24" aria-hidden="true"><path d="M8 5v14l11-7z"/></svg>
                        <svg class="dm-voice__icon-pause" viewBox="0 0 24 24" aria-hidden="true"><path d="M7 5h4v14H7zM13 5h4v14h-4z"/></svg>
                    </button>
                    <span class="dm-voice__wave" data-dm-voice-wave aria-hidden="true">
                        @foreach (range(1, 28) as $bar)<i style="--h: {{ 25 + (crc32($audio->id . '-' . $bar) % 70) }}%"></i>@endforeach
                    </span>
                    <span class="dm-voice__time" data-dm-voice-time>{{ $audio->durationLabel() }}</span>
                </div>
            @endif
            @foreach ($files as $file)
                <a href="{{ $file->url() }}" class="dm-file" download>
                    <span class="dm-file__icon" aria-hidden="true">{{ Str::upper(Str::limit(pathinfo($file->original_name, PATHINFO_EXTENSION) ?: 'FILE', 4, '')) }}</span>
                    <span class="min-w-0">
                        <span class="dm-file__name">{{ $file->original_name }}</span>
                        <span class="dm-file__meta">{{ $file->sizeLabel() }} · Download</span>
                    </span>
                </a>
            @endforeach
            @if (filled($message->body))
                <p class="dm-bubble" title="{{ $message->created_at->format('j M Y, H:i') }}">{{ $message->body }}</p>
            @endif
        </div>
    </div>
@endforeach
