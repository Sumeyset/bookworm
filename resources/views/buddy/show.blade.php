<x-app-layout>
    <x-slot name="title">{{ $read->title ?: $read->book->title }} · Buddy read</x-slot>

    <div class="page-section space-y-8">
        <a href="{{ route('buddy.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-burgundy hover:underline dark:text-[#E9A2AE]">&larr; All buddy reads</a>

        <section class="bw-card relative overflow-hidden p-6 sm:p-8">
            <div class="flex flex-col gap-6 sm:flex-row">
                <x-book-cover :book="$read->book" class="w-28 shrink-0 sm:w-36" />
                <div class="min-w-0 flex-1">
                    <p class="eyebrow">Buddy read · started by {{ $read->creator->name }}</p>
                    <h1 class="mt-2 font-display text-5xl font-bold leading-none text-ink dark:text-[#F4E9E2] sm:text-6xl">{{ $read->title ?: $read->book->title }}</h1>
                    <p class="mt-2 text-walnut dark:text-[#d4c2b9]">{{ $read->book->title }}{{ $read->book->author_name ? ' · ' . $read->book->author_name : '' }}</p>

                    {{-- Shared progress track --}}
                    <div class="mt-8">
                        <div class="relative h-3 rounded-full bg-parchment dark:bg-white/10">
                            @foreach ($members->where('joined', true) as $member)
                                <span class="absolute top-1/2 -translate-x-1/2 -translate-y-1/2 transition-all" style="left: {{ max(2, min(98, $member->percent)) }}%" title="{{ $member->name }} · {{ $member->percent }}%">
                                    <x-avatar :src="$member->profile_photo_url" :name="$member->name" size="sm" class="ring-2 ring-gold" />
                                </span>
                            @endforeach
                        </div>
                        <div class="mt-5 flex justify-between text-[11px] font-semibold uppercase tracking-wider text-tan"><span>First page</span><span>The end</span></div>
                    </div>
                </div>
            </div>
            <ul class="mt-6 grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                @foreach ($members as $member)
                    <li class="flex items-center gap-3 rounded-2xl bg-parchment/50 px-3 py-2 dark:bg-white/5">
                        <x-avatar :src="$member->profile_photo_url" :name="$member->name" size="sm" />
                        <span class="min-w-0 flex-1">
                            <span class="block truncate text-sm font-semibold text-ink dark:text-[#F4E9E2]">{{ $member->name }}</span>
                            <span class="block text-xs text-tan">{{ $member->joined ? ($member->percent === 100 ? 'Finished 🎉' : ($member->bookmark ? 'Page ' . $member->bookmark . ' · ' . $member->percent . '%' : 'Just starting')) : 'Invited' }}</span>
                        </span>
                    </li>
                @endforeach
            </ul>
            @if ($joined)
                <div class="mt-6 flex flex-wrap items-center gap-3 border-t border-dashed border-warmborder pt-5 dark:border-[#4B3035]">
                    <form method="POST" action="{{ route('books.shelf.update', $read->book) }}" class="flex items-center gap-2">
                        @csrf
                        <input type="hidden" name="status" value="reading">
                        <label for="my-page" class="text-sm font-semibold text-ink dark:text-[#F4E9E2]">My bookmark: page</label>
                        <input id="my-page" type="number" name="current_page" min="0" value="{{ $myItem?->current_page }}" class="bw-input w-24 py-1.5 text-center">
                        <span class="text-sm text-tan">of</span>
                        <input type="number" name="total_pages" min="1" value="{{ $myItem?->total_pages ?? $read->book->page_count }}" placeholder="?" class="bw-input w-24 py-1.5 text-center" aria-label="Total pages">
                        <button class="btn-primary min-h-0 py-2">Move bookmark</button>
                    </form>
                    <form method="POST" action="{{ route('buddy.leave', $read) }}" class="ml-auto" onsubmit="return confirm('Leave this buddy read?')">@csrf @method('DELETE')<button class="text-sm font-semibold text-tan underline hover:text-burgundy">Leave</button></form>
                </div>
            @else
                <form method="POST" action="{{ route('buddy.join', $read) }}" class="mt-6">@csrf<button class="btn-primary">Join this buddy read</button></form>
            @endif
        </section>

        <section aria-labelledby="talk-heading" class="space-y-4">
            <h2 id="talk-heading" class="font-display text-4xl font-bold text-ink dark:text-[#F4E9E2]">The conversation</h2>
            <p class="text-sm text-tan">Messages from pages you haven’t reached yet stay blurred until you choose to reveal them.</p>
            @forelse ($read->comments as $comment)
                @php $spoiler = $comment->page && $comment->page > $myPage && $comment->user_id !== auth()->id(); @endphp
                <article id="comment-{{ $comment->id }}" class="bw-card flex gap-3 p-4" x-data="{ hidden: {{ $spoiler ? 'true' : 'false' }} }">
                    <x-avatar :src="$comment->user->profile_photo_url" :name="$comment->user->name" size="sm" />
                    <div class="min-w-0 flex-1">
                        <p class="text-xs text-tan"><span class="font-semibold text-ink dark:text-[#F4E9E2]">{{ $comment->user->name }}</span> · {{ $comment->created_at->diffForHumans() }} @if ($comment->page)<span class="ml-1 rounded-full bg-parchment px-2 py-0.5 font-semibold text-walnut dark:bg-white/10 dark:text-[#e3d3ca]">p. {{ $comment->page }}</span>@endif</p>
                        <div class="relative mt-1">
                            <p class="whitespace-pre-line break-words text-[15px] leading-7 text-ink transition dark:text-[#F4E9E2]" :class="hidden && 'select-none blur-sm'">{{ $comment->body }}</p>
                            <button type="button" x-show="hidden" @click="hidden = false" class="absolute inset-0 flex items-center justify-center text-sm font-semibold text-burgundy dark:text-[#E9A2AE]">🙈 Spoiler from page {{ $comment->page }} — tap to reveal</button>
                        </div>
                    </div>
                </article>
            @empty
                <x-empty-state title="Say hello" message="Start the conversation — tag your message with the page you’re on." />
            @endforelse

            @if ($joined)
                <form method="POST" action="{{ route('buddy.comment', $read) }}" class="bw-card space-y-3 p-4">
                    @csrf
                    <label for="buddy-message" class="sr-only">Message</label>
                    <textarea id="buddy-message" name="body" rows="3" required maxlength="2000" placeholder="What do you think so far?" class="bw-input w-full"></textarea>
                    <div class="flex items-center justify-end gap-2">
                        <label for="buddy-page" class="text-sm text-tan">About page</label>
                        <input id="buddy-page" type="number" name="page" min="0" value="{{ $myItem?->current_page }}" class="bw-input w-24 py-1.5 text-center">
                        <button class="btn-primary">Send</button>
                    </div>
                </form>
            @endif
        </section>
    </div>
</x-app-layout>
