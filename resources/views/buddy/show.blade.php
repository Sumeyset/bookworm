<x-app-layout>
    <x-slot name="title">{{ $read->title ?: $read->book->title }} · Buddy read</x-slot>

    @php
        $me = auth()->user();
        $total = $myItem?->total_pages ?? $read->book->page_count;
        $myPercent = $myItem?->status === 'read' ? 100 : ($myItem?->progressPercent() ?? 0);
    @endphp

    <div class="page-section space-y-6">
        <a href="{{ route('buddy.index') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-burgundy hover:underline dark:text-[#E9A2AE]">&larr; All buddy reads</a>

        @unless ($joined)
            <div class="bw-card flex flex-wrap items-center gap-4 border-burgundy/30 p-4 ring-1 ring-burgundy/15">
                <x-avatar :src="$read->creator->profile_photo_url" :name="$read->creator->name" size="sm" />
                <p class="min-w-0 flex-1 text-sm text-ink dark:text-[#F4E9E2]"><b>{{ $read->creator->name }}</b> invited you to read this together.</p>
                <form method="POST" action="{{ route('buddy.join', $read) }}">@csrf<button class="btn-primary min-h-0 py-2">Join buddy read</button></form>
                <form method="POST" action="{{ route('buddy.leave', $read) }}">@csrf @method('DELETE')<input type="hidden" name="decline" value="1"><button class="text-sm font-semibold text-tan hover:text-burgundy hover:underline">Not now</button></form>
            </div>
        @endunless

        <div class="grid gap-6 lg:grid-cols-[minmax(0,1fr)_22rem]">
            {{-- Left: book, track, conversation --}}
            <div class="min-w-0 space-y-6">
                <section class="bw-card p-5 sm:p-7">
                    <div class="flex gap-5">
                        <x-book-cover :book="$read->book" class="w-24 shrink-0 sm:w-28" />
                        <div class="min-w-0 flex-1">
                            <p class="eyebrow">Buddy read · started by {{ $read->creator->id === $me->id ? 'you' : $read->creator->name }}</p>
                            <h1 class="mt-1 font-display text-4xl font-bold leading-none text-ink dark:text-[#F4E9E2] sm:text-5xl">{{ $read->title ?: $read->book->title }}</h1>
                            <p class="mt-2 text-sm text-walnut dark:text-[#d4c2b9]">{{ $read->book->title }}{{ $read->book->author_name ? ' · ' . $read->book->author_name : '' }}</p>
                        </div>
                    </div>

                    {{-- Everyone's bookmark on one track --}}
                    <div class="mt-8 px-3">
                        <div class="relative h-3 rounded-full bg-parchment dark:bg-white/10">
                            @foreach ($members->where('joined', true) as $member)
                                <span class="absolute top-1/2 -translate-x-1/2 -translate-y-1/2 transition-all" style="left: {{ max(3, min(97, $member->percent)) }}%; z-index: {{ $member->id === $me->id ? 2 : 1 }}" title="{{ $member->name }} · {{ $member->percent }}%">
                                    <x-avatar :src="$member->profile_photo_url" :name="$member->name" size="sm" class="{{ $member->id === $me->id ? '!ring-burgundy' : 'ring-gold' }} ring-2" />
                                </span>
                            @endforeach
                        </div>
                        <div class="mt-6 flex justify-between text-[11px] font-semibold uppercase tracking-wider text-tan"><span>First page</span><span>The end</span></div>
                    </div>
                </section>

                {{-- Conversation --}}
                <section aria-labelledby="talk-heading" class="bw-card overflow-hidden">
                    <div class="border-b border-warmborder px-5 py-4 dark:border-[#4B3035]">
                        <div class="flex flex-wrap items-start justify-between gap-3">
                            <div>
                                <h2 id="talk-heading" class="font-display text-3xl font-bold text-ink dark:text-[#F4E9E2]">The conversation</h2>
                                <p class="mt-0.5 text-sm text-tan">Tag each message with a page. Messages from pages you haven’t reached stay blurred until you tap them.</p>
                            </div>
                            @if ($joined)
                                <button type="button" class="bw-chip shrink-0" data-barnaby-discussion="{{ route('barnaby.discussion', $read) }}">
                                    <x-barnaby-avatar class="h-5 w-5" /> Discussion questions
                                </button>
                            @endif
                        </div>
                        <div class="barnaby-answer mt-4" data-barnaby-discussion-box hidden>
                            <x-barnaby-avatar class="h-9 w-9 shrink-0" />
                            <div class="min-w-0 flex-1">
                                <div class="barnaby-msg__bubble" data-barnaby-discussion-text></div>
                                <p class="mt-1.5 text-xs text-tan" data-barnaby-discussion-note></p>
                            </div>
                        </div>
                    </div>

                    <div class="space-y-3 bg-parchment/30 px-4 py-5 dark:bg-white/[0.02] sm:px-5">
                        @forelse ($read->comments as $comment)
                            @php
                                $mine = $comment->user_id === $me->id;
                                $spoiler = $comment->page && $comment->page > $myPage && ! $mine;
                            @endphp
                            <article id="comment-{{ $comment->id }}" class="flex scroll-mt-28 gap-2.5 {{ $mine ? 'flex-row-reverse' : '' }}" x-data="{ hidden: {{ $spoiler ? 'true' : 'false' }} }">
                                <x-avatar :src="$comment->user->profile_photo_url" :name="$comment->user->name" size="sm" class="mt-5" />
                                <div class="flex min-w-0 max-w-[80%] flex-col {{ $mine ? 'items-end' : 'items-start' }}">
                                    <p class="mb-1 px-1 text-xs text-tan">
                                        <span class="font-semibold text-ink dark:text-[#F4E9E2]">{{ $mine ? 'You' : $comment->user->name }}</span>
                                        · {{ $comment->created_at->diffForHumans(short: true) }}
                                        @if ($comment->page)<span class="ml-1 rounded-full bg-paper px-2 py-0.5 font-semibold text-walnut shadow-sm dark:bg-white/10 dark:text-[#e3d3ca]">p. {{ $comment->page }}</span>@endif
                                    </p>
                                    <div class="relative overflow-hidden rounded-2xl px-4 py-2.5 shadow-sm {{ $mine ? 'rounded-tr-sm bg-burgundy text-[#FFF8EF] dark:bg-burgundy-dark' : 'rounded-tl-sm bg-paper text-ink dark:bg-raised dark:text-[#F4E9E2]' }}">
                                        <p class="whitespace-pre-line break-words text-[15px] leading-6 transition" :class="hidden && 'select-none blur-sm'">{{ $comment->body }}</p>
                                        <button type="button" x-show="hidden" @click="hidden = false" class="absolute inset-0 flex items-center justify-center bg-paper/40 text-sm font-semibold text-burgundy dark:bg-black/30 dark:text-[#E9A2AE]">🙈 Page {{ $comment->page }} spoiler · tap to read</button>
                                    </div>
                                </div>
                            </article>
                        @empty
                            <div class="py-8 text-center">
                                <p class="text-3xl" aria-hidden="true">💬</p>
                                <p class="mt-2 font-serif text-xl text-ink dark:text-[#F4E9E2]">Say hello</p>
                                <p class="text-sm text-tan">First impressions, favourite lines, wild guesses — start the chat.</p>
                            </div>
                        @endforelse
                    </div>

                    @if ($joined)
                        <form method="POST" action="{{ route('buddy.comment', $read) }}" class="border-t border-warmborder p-4 dark:border-[#4B3035]" x-data="{ body: '' }">
                            @csrf
                            <label for="buddy-message" class="sr-only">Message</label>
                            <textarea id="buddy-message" name="body" x-model="body" rows="2" required maxlength="2000" placeholder="What do you think so far?" class="bw-input w-full resize-none" @keydown.enter.prevent.exact="body.trim() && $el.form.requestSubmit()"></textarea>
                            <div class="mt-2 flex flex-wrap items-center gap-2">
                                <label for="buddy-page" class="text-sm text-tan">I’m talking about page</label>
                                <input id="buddy-page" type="number" name="page" min="0" value="{{ $myItem?->current_page }}" class="bw-input w-20 py-1.5 text-center text-sm">
                                <span class="hidden text-xs text-tan sm:inline">Enter to send · Shift+Enter for a new line</span>
                                <button class="btn-primary ml-auto min-h-0 py-2" :disabled="!body.trim()">Send</button>
                            </div>
                        </form>
                    @endif
                </section>
            </div>

            {{-- Right: my bookmark, readers, invite --}}
            <aside class="space-y-6 lg:sticky lg:top-28 lg:self-start">
                @if ($joined)
                    <section class="bw-card p-5" aria-labelledby="bookmark-heading"
                             x-data="{ page: {{ (int) ($myItem?->current_page ?? 0) }}, total: {{ (int) ($total ?? 0) }} }">
                        <h2 id="bookmark-heading" class="font-serif text-xl font-semibold text-ink dark:text-[#F4E9E2]">Your bookmark</h2>
                        <p class="mt-0.5 text-sm text-tan">
                            @if ($myPercent === 100) You finished it 🎉 @else Move it as you read — everyone sees where you are. @endif
                        </p>
                        <form method="POST" action="{{ route('books.shelf.update', $read->book) }}" class="mt-4 space-y-3">
                            @csrf
                            <input type="hidden" name="status" :value="total && page >= total ? 'read' : 'reading'">
                            <div class="flex items-center gap-2">
                                <label for="my-page" class="sr-only">Current page</label>
                                <input id="my-page" type="number" name="current_page" min="0" :max="total || null" x-model.number="page" class="bw-input w-24 py-2 text-center text-lg font-semibold">
                                <span class="text-sm text-tan">of</span>
                                <label for="my-total" class="sr-only">Total pages</label>
                                <input id="my-total" type="number" name="total_pages" min="1" x-model.number="total" placeholder="pages" class="bw-input w-24 py-2 text-center">
                            </div>
                            <input type="range" min="0" :max="total || 500" x-model.number="page" class="w-full accent-burgundy" aria-label="Bookmark">
                            <div class="flex flex-wrap gap-1.5">
                                <button type="button" class="bw-chip !px-3 !py-1 text-xs" @click="page = Math.min(total || page + 10, page + 10)">+10</button>
                                <button type="button" class="bw-chip !px-3 !py-1 text-xs" @click="page = Math.min(total || page + 25, page + 25)">+25</button>
                                <button type="button" class="bw-chip !px-3 !py-1 text-xs" x-show="total" @click="page = total">Finished</button>
                            </div>
                            <button class="btn-primary w-full">Save bookmark</button>
                        </form>
                    </section>
                @endif

                <section class="bw-card p-5" aria-labelledby="readers-heading">
                    <h2 id="readers-heading" class="font-serif text-xl font-semibold text-ink dark:text-[#F4E9E2]">Readers <span class="text-sm font-normal text-tan">{{ $members->count() }}</span></h2>
                    <ul class="mt-3 space-y-3">
                        @foreach ($members as $member)
                            <li class="flex items-center gap-3">
                                <a href="{{ route('users.show', $member->username) }}" class="shrink-0"><x-avatar :src="$member->profile_photo_url" :name="$member->name" size="sm" /></a>
                                <div class="min-w-0 flex-1">
                                    <p class="flex justify-between gap-2 text-sm">
                                        <span class="truncate font-semibold text-ink dark:text-[#F4E9E2]">{{ $member->id === $me->id ? 'You' : $member->name }}</span>
                                        <span class="shrink-0 text-xs text-tan">{{ $member->joined ? ($member->percent === 100 ? 'Finished 🎉' : ($member->bookmark ? 'p. ' . $member->bookmark : 'Starting')) : 'Invited' }}</span>
                                    </p>
                                    <div class="mt-1 h-1.5 rounded-full bg-parchment dark:bg-white/10">
                                        <div class="h-full rounded-full {{ $member->joined ? 'bg-gradient-to-r from-burgundy to-[#b84560]' : '' }}" style="width: {{ $member->joined ? $member->percent : 0 }}%"></div>
                                    </div>
                                </div>
                            </li>
                        @endforeach
                    </ul>

                    @if ($joined)
                        <div class="mt-5 border-t border-warmborder pt-4 dark:border-[#4B3035]" x-data="{ open: false }">
                            @if ($invitable->isNotEmpty())
                                <button type="button" @click="open = !open" class="btn-ghost w-full" :aria-expanded="open">+ Invite more friends</button>
                                <form x-show="open" x-cloak method="POST" action="{{ route('buddy.invite', $read) }}" class="mt-3 space-y-2">
                                    @csrf
                                    <div class="max-h-52 space-y-1 overflow-y-auto">
                                        @foreach ($invitable as $friend)
                                            <label class="flex cursor-pointer items-center gap-3 rounded-xl p-2 hover:bg-parchment/60 dark:hover:bg-white/5">
                                                <input type="checkbox" name="invitees[]" value="{{ $friend->id }}" class="rounded border-warmborder text-burgundy focus:ring-burgundy/30">
                                                <x-avatar :src="$friend->profile_photo_url" :name="$friend->name" size="sm" class="!h-7 !w-7 !text-xs" />
                                                <span class="truncate text-sm font-semibold text-ink dark:text-[#F4E9E2]">{{ $friend->name }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                    <button class="btn-primary w-full min-h-0 py-2">Send invitations</button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('buddy.leave', $read) }}" class="mt-3 text-center" onsubmit="return confirm('Leave this buddy read?')">
                                @csrf @method('DELETE')
                                <button class="text-sm font-semibold text-tan hover:text-burgundy hover:underline">Leave buddy read</button>
                            </form>
                        </div>
                    @endif
                </section>
            </aside>
        </div>
    </div>
</x-app-layout>
