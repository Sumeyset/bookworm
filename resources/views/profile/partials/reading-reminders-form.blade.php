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

@if (\App\Support\Push::configured())
    <div class="mt-8 border-t border-warmborder pt-6 dark:border-[#4B3035]" data-push-settings
         data-key="{{ config('webpush.public_key') }}" data-subscribe-url="{{ route('push.subscribe') }}" data-unsubscribe-url="{{ route('push.unsubscribe') }}">
        <p class="font-semibold text-ink dark:text-[#F4E9E2]">Notifications on this device</p>
        <p class="mt-1 text-sm text-walnut dark:text-[#d4c2b9]">Messages, club news, badges and reminders, even when Bookworm is closed. On iPhone, add Bookworm to your Home Screen first (Share → Add to Home Screen).</p>
        <div class="mt-3 flex flex-wrap items-center gap-3">
            <button type="button" class="btn-primary" data-push-toggle disabled>Checking…</button>
            <span class="text-sm text-tan" data-push-note></span>
        </div>
    </div>
    <script>
        (() => {
            const box = document.querySelector('[data-push-settings]');
            const button = box.querySelector('[data-push-toggle]');
            const note = box.querySelector('[data-push-note]');
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
            const supported = 'serviceWorker' in navigator && 'PushManager' in window && 'Notification' in window;
            const keyBytes = (base64) => {
                const padded = (base64 + '='.repeat((4 - base64.length % 4) % 4)).replace(/-/g, '+').replace(/_/g, '/');
                return Uint8Array.from(atob(padded), (c) => c.charCodeAt(0));
            };
            const send = (url, method, body) => fetch(url, { method, credentials: 'same-origin', headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf }, body: JSON.stringify(body) });

            async function refresh() {
                if (!supported) {
                    button.textContent = 'Not available here';
                    note.textContent = 'This browser can’t show notifications.';
                    return;
                }
                const registration = await navigator.serviceWorker.ready;
                const subscription = await registration.pushManager.getSubscription();
                button.disabled = Notification.permission === 'denied';
                button.textContent = subscription ? 'Turn off on this device' : 'Turn on notifications';
                button.dataset.on = subscription ? '1' : '';
                note.textContent = Notification.permission === 'denied' ? 'Notifications are blocked in your browser settings.' : (subscription ? '✓ On for this device' : '');
            }

            button.addEventListener('click', async () => {
                button.disabled = true;
                try {
                    const registration = await navigator.serviceWorker.ready;
                    const existing = await registration.pushManager.getSubscription();
                    if (existing) {
                        await send(box.dataset.unsubscribeUrl, 'DELETE', { endpoint: existing.endpoint });
                        await existing.unsubscribe();
                    } else {
                        if (await Notification.requestPermission() !== 'granted') return;
                        const subscription = await registration.pushManager.subscribe({ userVisibleOnly: true, applicationServerKey: keyBytes(box.dataset.key) });
                        const json = subscription.toJSON();
                        const encoding = (PushManager.supportedContentEncodings || ['aes128gcm'])[0];
                        await send(box.dataset.subscribeUrl, 'POST', { ...json, contentEncoding: encoding });
                    }
                } catch (error) {
                    note.textContent = 'Something went wrong — please try again.';
                } finally {
                    refresh();
                }
            });

            refresh();
        })();
    </script>
@endif
