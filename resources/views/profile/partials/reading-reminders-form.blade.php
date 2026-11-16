<form method="POST" action="{{ route('settings.reminders') }}" class="space-y-5">
    @csrf
    @method('PATCH')
    <input type="hidden" name="timezone" value="{{ $user->timezone }}" data-detect-timezone>

    <label class="flex items-start gap-3">
        <input type="checkbox" name="streak_reminder" value="1" class="mt-1 rounded border-warmborder text-burgundy focus:ring-burgundy" @checked($user->streak_reminder)>
        <span>
            <span class="block font-semibold text-ink dark:text-[#F4E9E2]">Remind me before my streak ends</span>
            <span class="block text-sm text-walnut dark:text-[#d4c2b9]">One notification, only on days you haven’t read yet and your streak would end at midnight.</span>
        </span>
    </label>

    <div>
        <label for="reminder_hour" class="block text-sm font-semibold text-ink dark:text-[#F4E9E2]">Remind me at</label>
        <select id="reminder_hour" name="reminder_hour" class="bw-input mt-1 w-40">
            @foreach (range(6, 23) as $hour)
                <option value="{{ $hour }}" @selected((int) $user->reminder_hour === $hour)>{{ \Carbon\Carbon::createFromTime($hour)->format('g A') }}</option>
            @endforeach
        </select>
        <p class="mt-1 text-xs text-tan">Your time zone: <span data-timezone-label>{{ $user->timezone ?: 'not set yet' }}</span></p>
    </div>

    <p class="rounded-2xl bg-parchment/70 px-4 py-3 text-sm text-walnut dark:bg-white/5 dark:text-[#d4c2b9]">
        🧊 You have <b>{{ $user->streak_freezes }}</b> {{ Str::plural('streak freeze', $user->streak_freezes) }}. A freeze covers one missed day by itself.
        <a href="{{ route('shop.index') }}#streak-freeze" class="font-semibold text-burgundy underline underline-offset-2 dark:text-[#F0A8B4]">Get one in the Tree shop</a>
    </p>

    <div class="flex items-center gap-4">
        <button class="btn-primary">Save reminders</button>
        @if (session('status') === 'reminders-updated')
            <p class="text-sm text-tan">Saved.</p>
        @endif
    </div>
</form>
<script>
    // Fill in the reader's time zone from their browser, so reminders come at their own hour.
    document.querySelectorAll('[data-detect-timezone]').forEach((input) => {
        try {
            const zone = Intl.DateTimeFormat().resolvedOptions().timeZone;
            if (zone && !input.value) {
                input.value = zone;
                document.querySelectorAll('[data-timezone-label]').forEach((label) => { label.textContent = zone; });
            }
        } catch {}
    });
</script>
