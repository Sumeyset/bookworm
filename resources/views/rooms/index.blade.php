<x-app-layout>
    <x-slot name="title">Reading rooms · Bookworm</x-slot>
    <x-slot name="header">
        <x-page-header eyebrow="Read together, quietly" title="Reading rooms" subtitle="Start a timer, invite your friends, and read at the same time — like a silent book club." />
    </x-slot>

    <div class="page-section grid gap-8 lg:grid-cols-[minmax(0,1fr)_22rem]">
        <section aria-labelledby="live-rooms">
            <h2 id="live-rooms" class="mb-4 font-display text-3xl font-bold text-ink dark:text-[#f4e9e2]">Open now</h2>
            @forelse ($live as $room)
                <a href="{{ route('rooms.show', $room) }}" class="room-card">
                    <span class="room-card__candle" aria-hidden="true">🕯️</span>
                    <span class="min-w-0 flex-1">
                        <span class="block truncate font-serif text-lg font-semibold text-ink dark:text-[#f4e9e2]">{{ $room->title }}</span>
                        <span class="block text-sm text-tan">{{ $room->host->name }} · {{ $room->members_count }} {{ Str::plural('reader', $room->members_count) }} · {{ (int) ceil(now()->diffInSeconds($room->ends_at) / 60) }} min left</span>
                    </span>
                    <span class="btn-primary min-h-0 py-2">Join</span>
                </a>
            @empty
                <p class="text-walnut dark:text-[#d4c2b9]">No rooms open among your friends right now. Start one — they’ll get a notification.</p>
            @endforelse
        </section>

        <form method="POST" action="{{ route('rooms.store') }}" class="bw-card space-y-4 p-5 lg:sticky lg:top-28 lg:self-start" x-data="{ minutes: 25 }">
            @csrf
            <h2 class="font-serif text-xl font-semibold text-ink dark:text-[#f4e9e2]">Start a room</h2>
            <label class="block text-sm font-semibold text-ink dark:text-[#f4e9e2]">Name <span class="font-normal text-tan">(optional)</span>
                <input name="title" maxlength="80" placeholder="Sunday morning pages" class="bw-input mt-1 w-full">
            </label>
            <fieldset>
                <legend class="text-sm font-semibold text-ink dark:text-[#f4e9e2]">How long</legend>
                <div class="mt-2 flex gap-1.5">
                    @foreach ($lengths as $length)
                        <label class="mood-chip" :class="minutes === {{ $length }} && 'is-on'"><input type="radio" name="minutes" value="{{ $length }}" class="sr-only" x-model.number="minutes">{{ $length }} min</label>
                    @endforeach
                </div>
            </fieldset>
            @if ($books->isNotEmpty())
                <label class="block text-sm font-semibold text-ink dark:text-[#f4e9e2]">I’ll be reading
                    <select name="book_id" class="bw-input mt-1 w-full">
                        @foreach ($books as $book)<option value="{{ $book->id }}">{{ Str::limit($book->title, 48) }}</option>@endforeach
                        <option value="">Something else</option>
                    </select>
                </label>
            @endif
            <button class="btn-primary w-full">Open the room</button>
        </form>
    </div>
</x-app-layout>
