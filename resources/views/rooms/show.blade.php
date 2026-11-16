<x-app-layout>
    @push('scripts')
        @vite('resources/js/rooms.js')
    @endpush
    <x-slot name="title">{{ $room->title }} · Reading room</x-slot>

    <div class="page-section" data-room data-state-url="{{ route('rooms.state', $room) }}" data-message-url="{{ route('rooms.message', $room) }}" data-ends="{{ $room->ends_at->toIso8601String() }}">
        <a href="{{ route('rooms.index') }}" class="text-sm font-semibold text-burgundy hover:underline dark:text-[#E9A2AE]">&larr; Reading rooms</a>

        <div class="room mt-4">
            <section class="room__stage">
                <p class="room__kicker">🕯️ Reading room · hosted by {{ $room->host->name }}</p>
                <h1 class="room__title">{{ $room->title }}</h1>
                <div class="room__timer" aria-live="polite">
                    <svg viewBox="0 0 120 120" aria-hidden="true"><circle cx="60" cy="60" r="54" class="room__track"/><circle cx="60" cy="60" r="54" class="room__ring" data-room-ring/></svg>
                    <span data-room-clock>--:--</span>
                </div>
                <p class="room__note" data-room-note>{{ $room->isLive() ? 'Phones down, books open. Read until the timer ends.' : 'This session has ended.' }}</p>

                @if (! $joined && $room->isLive())
                    <form method="POST" action="{{ route('rooms.join', $room) }}" class="mx-auto mt-5 flex max-w-sm flex-wrap justify-center gap-2">
                        @csrf
                        @if ($books->isNotEmpty())
                            <select name="book_id" class="bw-input min-w-0 flex-1 py-2" aria-label="What you'll read">
                                @foreach ($books as $book)<option value="{{ $book->id }}">{{ Str::limit($book->title, 40) }}</option>@endforeach
                                <option value="">Something else</option>
                            </select>
                        @endif
                        <button class="btn-primary">Join and read</button>
                    </form>
                @endif

                <div class="room__done" data-room-done hidden>
                    <p class="font-serif text-2xl font-semibold">Time’s up — well read! 📚</p>
                    <p class="mt-1 text-sm opacity-80">Move your bookmark to log the pages you read.</p>
                    <a href="{{ route('library.index') }}" class="btn-primary mt-3">Log my pages</a>
                </div>
            </section>

            <aside class="room__side">
                <h2 class="font-serif text-lg font-semibold">Reading here</h2>
                <ul class="room__people" data-room-members></ul>

                <h2 class="mt-5 font-serif text-lg font-semibold">Quiet chat</h2>
                <div class="room__chat" data-room-chat aria-live="polite"></div>
                @if ($joined && $room->isLive())
                    <form class="mt-2 flex gap-2" data-room-form>
                        <input name="body" maxlength="280" autocomplete="off" placeholder="A whisper…" class="bw-input min-w-0 flex-1 py-2" aria-label="Message">
                        <button class="btn-primary min-h-0 py-2">Send</button>
                    </form>
                    <form method="POST" action="{{ route('rooms.leave', $room) }}" class="mt-3 text-center" onsubmit="return confirm('{{ $room->host_id === auth()->id() ? 'Leave and end the session for everyone?' : 'Leave this room?' }}')">
                        @csrf @method('DELETE')
                        <button class="text-sm font-semibold text-tan hover:text-burgundy hover:underline">Leave</button>
                    </form>
                @endif
            </aside>
        </div>
    </div>
</x-app-layout>
