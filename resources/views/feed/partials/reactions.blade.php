{{-- Like button and replies for a post or review. Expects $item (with likes_count, comments_count, liked_by_viewer, comments). --}}
@php
    $type = $item->getMorphClass();
    $commentsOpen = $item->comments_count > 0 && $item->comments_count <= 2;
@endphp
<div class="border-t border-warmborder/70 dark:border-[#4B3035]" x-data="{ open: {{ $commentsOpen ? 'true' : 'false' }} }">
    <div class="flex items-center gap-1 px-4 py-2 sm:px-5">
        <button type="button"
                data-like-url="{{ route('reactions.like', [$type, $item->id]) }}"
                aria-pressed="{{ $item->liked_by_viewer ? 'true' : 'false' }}"
                class="bw-like focus-ring">
            <svg class="h-5 w-5" viewBox="0 0 24 24" aria-hidden="true"><path d="M12 20.5s-7.5-4.6-9.3-9.2C1.4 8 3.4 4.5 7 4.5c2 0 3.6 1.1 5 3 1.4-1.9 3-3 5-3 3.6 0 5.6 3.5 4.3 6.8-1.8 4.6-9.3 9.2-9.3 9.2Z"/></svg>
            <span data-like-count>{{ $item->likes_count }}</span>
            <span class="sr-only">likes</span>
        </button>
        <button type="button" @click="open = !open; $nextTick(() => open && $refs.reply.focus())" class="focus-ring inline-flex items-center gap-1.5 rounded-full px-3 py-1.5 text-sm font-semibold text-tan transition hover:bg-parchment hover:text-burgundy dark:hover:bg-white/5 dark:hover:text-[#E9A2AE]" :aria-expanded="open">
            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 5.5h16v10H9l-5 4v-14Z"/></svg>
            {{ $item->comments_count }} <span class="hidden sm:inline">{{ Str::plural('reply', $item->comments_count) }}</span>
        </button>
        @if ($item->book)
            <a href="{{ route('books.show', $item->book) }}" class="ml-auto hidden max-w-[45%] truncate rounded-full px-3 py-1.5 text-xs font-semibold text-tan transition hover:bg-parchment hover:text-burgundy dark:hover:bg-white/5 sm:inline-block">📖 {{ $item->book->title }}</a>
        @endif
    </div>

    <div x-show="open" x-cloak class="space-y-3 bg-parchment/40 px-4 pb-4 pt-3 dark:bg-white/[0.03] sm:px-5">
        @foreach ($item->comments as $comment)
            <div class="flex gap-2.5">
                <x-avatar :src="$comment->user->profile_photo_url" :name="$comment->user->name" size="sm" />
                <div class="min-w-0 flex-1 rounded-2xl rounded-tl-sm bg-paper px-3.5 py-2 shadow-sm dark:bg-raised">
                    <p class="text-xs text-tan">
                        <a href="{{ route('users.show', $comment->user->username) }}" class="font-semibold text-burgundy hover:underline dark:text-[#E9A2AE]">{{ $comment->user->name }}</a>
                        · {{ $comment->created_at->diffForHumans() }}
                        @if ($comment->user_id === auth()->id())
                            <form method="POST" action="{{ route('comments.destroy', $comment) }}" class="inline">@csrf @method('DELETE')<button class="ml-1 underline hover:text-burgundy">Delete</button></form>
                        @endif
                    </p>
                    <p class="mt-0.5 whitespace-pre-line break-words text-sm text-ink dark:text-[#F4E9E2]">{{ $comment->body }}</p>
                </div>
            </div>
        @endforeach
        <form method="POST" action="{{ route('reactions.comment', [$type, $item->id]) }}" class="flex items-end gap-2">
            @csrf
            <label class="sr-only" for="reply-{{ $type }}-{{ $item->id }}">Write a reply</label>
            <textarea x-ref="reply" id="reply-{{ $type }}-{{ $item->id }}" name="body" rows="1" maxlength="1000" required placeholder="Write a reply…" class="bw-input min-h-10 flex-1 resize-none py-2 text-sm" @keydown.enter.prevent.exact="$el.form.requestSubmit()"></textarea>
            <button type="submit" class="btn-primary min-h-10 px-4 py-2">Reply</button>
        </form>
    </div>
</div>
