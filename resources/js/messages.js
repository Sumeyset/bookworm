// Direct messages: the chat (send without reloading, new messages appear on
// their own), the "New message" picker, and the "Send to" sheet for posts.
import { fetchReaders, readerAvatar } from './reader-search.js';
import { preparePhoto } from './photo-prep.js';

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

async function json(url, options = {}) {
    const response = await fetch(url, {
        credentials: 'same-origin',
        ...options,
        headers: {
            Accept: 'application/json',
            ...(options.body ? { 'Content-Type': 'application/json' } : {}),
            'X-CSRF-TOKEN': csrf(),
            ...options.headers,
        },
    });
    const payload = await response.json().catch(() => ({}));
    if (!response.ok) {
        throw new Error(payload.errors ? Object.values(payload.errors).flat().join(' ') : payload.message || 'Something went wrong.');
    }
    return payload;
}

function setBadge(count) {
    document.querySelectorAll('[data-dm-badge]').forEach((badge) => {
        badge.textContent = count > 9 ? '9+' : String(count);
        badge.hidden = count < 1;
    });
}

/* ---------------- the chat ---------------- */

const MAX_PHOTOS = 10;
const MAX_FILES = 5;
const MAX_VOICE_SECONDS = 300;

const formatTime = (seconds) => {
    const total = Math.max(0, Math.round(seconds || 0));
    return `${Math.floor(total / 60)}:${String(total % 60).padStart(2, '0')}`;
};

function initChat(form) {
    const scroller = document.querySelector('[data-dm-scroll]');
    const list = document.querySelector('[data-dm-messages]');
    const seen = document.querySelector('[data-dm-seen]');
    const input = form.querySelector('[data-dm-input]');
    const sendButton = form.querySelector('[data-dm-send]');
    const recordButton = form.querySelector('[data-dm-record]');
    const tray = form.querySelector('[data-dm-tray]');
    const compose = form.querySelector('[data-dm-compose]');
    const recorderBox = form.querySelector('[data-dm-recorder]');
    const me = form.dataset.me;
    let sending = false;
    let seenId = Number(list.dataset.seenId) || null;
    const pending = { photos: [], files: [] }; // photos: { id, file, url }, files: { id, file }
    let pendingIds = 0;

    const lastId = () => Math.max(0, ...[...list.querySelectorAll('[data-message-id]')].map((row) => Number(row.dataset.messageId)));
    const nearBottom = () => scroller.scrollHeight - scroller.scrollTop - scroller.clientHeight < 140;
    const toBottom = () => { scroller.scrollTop = scroller.scrollHeight; };

    // Messages from the same person a few minutes apart sit together.
    const group = () => {
        let previous = null;
        list.querySelectorAll('.dm-row').forEach((row) => {
            const same = previous
                && previous.dataset.sender === row.dataset.sender
                && new Date(row.dataset.time) - new Date(previous.dataset.time) < 5 * 60 * 1000;
            row.classList.toggle('dm-row--same', Boolean(same));
            previous = row;
        });
    };

    // "Seen" sits under my latest message once the other reader has opened the chat.
    const placeSeen = () => {
        const mine = [...list.querySelectorAll(`.dm-row[data-sender="${me}"]`)].pop();
        const last = list.querySelector('.dm-row:last-child');
        const show = Boolean(seenId && mine && Number(mine.dataset.messageId) === seenId && mine === last);
        seen.hidden = !show;
    };

    const append = (html) => {
        const template = document.createElement('template');
        template.innerHTML = html;
        [...template.content.children].forEach((row) => {
            if (!list.querySelector(`[data-message-id="${row.dataset.messageId}"]`)) list.append(row);
        });
        group();
        placeSeen();
    };

    const hasSomething = () => input.value.trim() || pending.photos.length || pending.files.length;
    const refreshButtons = () => {
        const ready = Boolean(hasSomething());
        sendButton.hidden = !ready;
        sendButton.disabled = sending;
        recordButton.hidden = ready;
    };

    const autosize = () => {
        input.style.height = 'auto';
        input.style.height = `${Math.min(input.scrollHeight, 128)}px`;
        refreshButtons();
    };

    /* ----- photos & files waiting to be sent ----- */

    const renderTray = () => {
        tray.replaceChildren();
        const removeButton = (onRemove, label) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = 'dm-tray__remove';
            button.textContent = '×';
            button.setAttribute('aria-label', label);
            button.addEventListener('click', onRemove);
            return button;
        };
        pending.photos.forEach((photo) => {
            const item = document.createElement('div');
            item.className = 'dm-tray__item';
            const image = document.createElement('img');
            image.src = photo.url;
            image.alt = '';
            item.append(image, removeButton(() => {
                URL.revokeObjectURL(photo.url);
                pending.photos = pending.photos.filter((entry) => entry.id !== photo.id);
                renderTray();
            }, 'Remove photo'));
            tray.append(item);
        });
        pending.files.forEach((entry) => {
            const item = document.createElement('div');
            item.className = 'dm-tray__item';
            const chip = document.createElement('div');
            chip.className = 'dm-tray__file';
            const icon = document.createElement('span');
            icon.className = 'dm-file__icon';
            icon.textContent = (entry.file.name.split('.').pop() || 'FILE').slice(0, 4).toUpperCase();
            const name = document.createElement('span');
            name.className = 'dm-file__name';
            name.textContent = entry.file.name;
            chip.append(icon, name);
            item.append(chip, removeButton(() => {
                pending.files = pending.files.filter((other) => other.id !== entry.id);
                renderTray();
            }, `Remove ${entry.file.name}`));
            tray.append(item);
        });
        tray.hidden = !pending.photos.length && !pending.files.length;
        refreshButtons();
    };

    form.querySelector('[data-dm-photos]').addEventListener('change', async (event) => {
        const files = [...event.target.files].filter((file) => file.type.startsWith('image/'));
        event.target.value = '';
        const room = MAX_PHOTOS - pending.photos.length;
        if (files.length > room) window.bookwormToast?.(`You can send up to ${MAX_PHOTOS} photos at once.`, 'error');
        for (const file of files.slice(0, Math.max(0, room))) {
            try {
                // GIFs keep their animation; other photos are shrunk before sending.
                const ready = file.type === 'image/gif' ? file : (await preparePhoto(file)).file;
                pending.photos.push({ id: ++pendingIds, file: ready, url: URL.createObjectURL(ready) });
            } catch (error) {
                window.bookwormToast?.(error.message, 'error');
            }
        }
        renderTray();
        input.focus();
    });

    form.querySelector('[data-dm-files]').addEventListener('change', (event) => {
        const files = [...event.target.files];
        event.target.value = '';
        const room = MAX_FILES - pending.files.length;
        if (files.length > room) window.bookwormToast?.(`You can send up to ${MAX_FILES} files at once.`, 'error');
        files.slice(0, Math.max(0, room)).forEach((file) => {
            if (file.size > 20 * 1048576) {
                window.bookwormToast?.(`“${file.name}” is larger than 20 MB.`, 'error');
                return;
            }
            pending.files.push({ id: ++pendingIds, file });
        });
        renderTray();
        input.focus();
    });

    // Paste a photo straight into the box.
    input.addEventListener('paste', (event) => {
        const images = [...(event.clipboardData?.files || [])].filter((file) => file.type.startsWith('image/'));
        if (!images.length) return;
        event.preventDefault();
        const picker = form.querySelector('[data-dm-photos]');
        const transfer = new DataTransfer();
        images.forEach((file) => transfer.items.add(file));
        picker.files = transfer.files;
        picker.dispatchEvent(new Event('change'));
    });

    /* ----- sending ----- */

    function pendingRow(label) {
        const row = document.createElement('div');
        row.className = 'dm-row dm-row--mine is-sending';
        row.dataset.sender = me;
        row.dataset.time = new Date().toISOString();
        const bubble = document.createElement('p');
        bubble.className = 'dm-bubble';
        bubble.textContent = label;
        row.append(bubble);
        list.append(row);
        group();
        seen.hidden = true;
        toBottom();
        return row;
    }

    async function upload(data, label) {
        sending = true;
        refreshButtons();
        const row = pendingRow(label);
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                credentials: 'same-origin',
                headers: { Accept: 'application/json', 'X-CSRF-TOKEN': csrf() },
                body: data,
            });
            const payload = await response.json().catch(() => ({}));
            if (!response.ok) {
                throw new Error(response.status === 413
                    ? 'That’s too big to send in one go. Try fewer or smaller files.'
                    : payload.errors ? Object.values(payload.errors).flat().join(' ') : payload.message || 'Couldn’t send that. Please try again.');
            }
            row.remove();
            append(payload.html);
            toBottom();
            return true;
        } catch (error) {
            row.remove();
            window.bookwormToast?.(error.message, 'error');
            return false;
        } finally {
            sending = false;
            refreshButtons();
        }
    }

    async function send(event) {
        event?.preventDefault();
        if (!hasSomething() || sending) return;
        const body = input.value.trim();
        const data = new FormData();
        if (body) data.append('body', body);
        pending.photos.forEach((photo) => data.append('photos[]', photo.file));
        pending.files.forEach((entry) => data.append('files[]', entry.file));
        const label = pending.photos.length ? `Sending ${pending.photos.length === 1 ? 'photo' : `${pending.photos.length} photos`}…`
            : pending.files.length ? 'Sending file…' : body;

        const saved = { body, photos: pending.photos, files: pending.files };
        input.value = '';
        pending.photos = [];
        pending.files = [];
        renderTray();
        autosize();

        const ok = await upload(data, label);
        if (ok) {
            saved.photos.forEach((photo) => URL.revokeObjectURL(photo.url));
        } else {
            input.value = saved.body;
            pending.photos = saved.photos;
            pending.files = saved.files;
            renderTray();
            autosize();
        }
        input.focus();
    }

    /* ----- voice messages ----- */

    let recorder = null;
    let chunks = [];
    let startedAt = 0;
    let timer = null;
    let meter = null;
    let discard = false;

    const stopTracks = () => recorder?.stream.getTracks().forEach((track) => track.stop());
    const showRecorder = (on) => {
        recorderBox.hidden = !on;
        compose.hidden = on;
        tray.hidden = on || (!pending.photos.length && !pending.files.length);
    };

    async function startRecording() {
        if (!navigator.mediaDevices?.getUserMedia || typeof MediaRecorder === 'undefined') {
            window.bookwormToast?.('Voice messages aren’t supported in this browser.', 'error');
            return;
        }
        let stream;
        try {
            stream = await navigator.mediaDevices.getUserMedia({ audio: true });
        } catch {
            window.bookwormToast?.('Allow microphone access to record a voice message.', 'error');
            return;
        }
        const type = ['audio/webm;codecs=opus', 'audio/webm', 'audio/mp4', 'audio/ogg'].find((candidate) => MediaRecorder.isTypeSupported?.(candidate));
        recorder = new MediaRecorder(stream, type ? { mimeType: type } : undefined);
        chunks = [];
        discard = false;
        recorder.addEventListener('dataavailable', (event) => { if (event.data.size) chunks.push(event.data); });
        recorder.addEventListener('stop', onRecorded);
        recorder.start(250);
        startedAt = Date.now();
        showRecorder(true);

        // A little live level meter while recording.
        const bars = form.querySelector('[data-dm-record-bars]');
        bars.replaceChildren();
        try {
            const context = new AudioContext();
            const analyser = context.createAnalyser();
            analyser.fftSize = 256;
            context.createMediaStreamSource(stream).connect(analyser);
            const levels = new Uint8Array(analyser.frequencyBinCount);
            meter = { context, stop: false };
            const draw = () => {
                if (meter?.stop) return;
                analyser.getByteFrequencyData(levels);
                const level = levels.reduce((sum, value) => sum + value, 0) / levels.length / 255;
                const bar = document.createElement('i');
                bar.style.height = `${Math.max(18, Math.min(100, level * 300))}%`;
                bars.append(bar);
                if (bars.children.length > 60) bars.firstElementChild.remove();
                window.setTimeout(() => window.requestAnimationFrame(draw), 90);
            };
            draw();
        } catch {
            meter = null;
        }

        const time = form.querySelector('[data-dm-record-time]');
        time.textContent = '0:00';
        timer = window.setInterval(() => {
            const seconds = (Date.now() - startedAt) / 1000;
            time.textContent = formatTime(seconds);
            if (seconds >= MAX_VOICE_SECONDS) finishRecording(false);
        }, 250);
    }

    function finishRecording(cancel) {
        if (!recorder || recorder.state === 'inactive') return;
        discard = cancel;
        window.clearInterval(timer);
        if (meter) {
            meter.stop = true;
            meter.context.close().catch(() => {});
        }
        recorder.stop();
        stopTracks();
    }

    async function onRecorded() {
        const duration = Math.min(MAX_VOICE_SECONDS, (Date.now() - startedAt) / 1000);
        showRecorder(false);
        const type = recorder.mimeType || 'audio/webm';
        recorder = null;
        if (discard || duration < 0.6 || !chunks.length) {
            if (!discard) window.bookwormToast?.('That was too short — hold on a little longer.', 'error');
            return;
        }
        const extension = type.includes('mp4') ? 'm4a' : type.includes('ogg') ? 'ogg' : 'webm';
        const blob = new Blob(chunks, { type: type.split(';')[0] });
        const data = new FormData();
        data.append('voice', new File([blob], `voice-message.${extension}`, { type: blob.type }));
        data.append('voice_duration', duration.toFixed(2));
        await upload(data, 'Sending voice message…');
    }

    recordButton.addEventListener('click', startRecording);
    form.querySelector('[data-dm-record-cancel]').addEventListener('click', () => finishRecording(true));
    form.querySelector('[data-dm-record-send]').addEventListener('click', () => finishRecording(false));

    /* ----- new messages arriving ----- */

    async function poll() {
        if (document.hidden) return;
        try {
            const stick = nearBottom();
            const payload = await json(`${form.dataset.pollUrl}?after=${lastId()}`);
            seenId = payload.seen_id ?? seenId;
            if (payload.html) {
                append(payload.html);
                if (stick) toBottom();
            } else {
                placeSeen();
            }
            setBadge(payload.unread);
        } catch {
            // Offline for a moment: try again next time.
        }
    }

    group();
    placeSeen();
    toBottom();
    window.addEventListener('load', toBottom, { once: true });
    list.querySelectorAll('img').forEach((image) => image.addEventListener('load', () => { if (nearBottom()) toBottom(); }, { once: true }));
    input.addEventListener('input', autosize);
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) send(event);
    });
    form.addEventListener('submit', send);
    window.setInterval(poll, 4000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) poll(); });
    refreshButtons();
    if (window.matchMedia('(pointer: fine)').matches) input.focus();
}

/* ---------------- voice message players ---------------- */

function initVoicePlayers() {
    let current = null; // { box, audio }

    const update = (box, audio) => {
        const duration = Number(box.dataset.duration) || audio.duration || 0;
        const progress = duration ? Math.min(1, audio.currentTime / duration) : 0;
        const bars = box.querySelectorAll('[data-dm-voice-wave] i');
        const played = Math.round(progress * bars.length);
        bars.forEach((bar, index) => bar.classList.toggle('is-played', index < played));
        box.querySelector('[data-dm-voice-time]').textContent = formatTime(audio.paused && !audio.currentTime ? duration : audio.currentTime);
    };

    const stop = () => {
        if (!current) return;
        current.audio.pause();
        current.box.classList.remove('is-playing');
    };

    document.addEventListener('click', (event) => {
        const box = event.target.closest('[data-dm-voice]');
        if (!box) return;
        if (current?.box !== box) {
            stop();
            const audio = new Audio(box.dataset.src);
            audio.preload = 'auto';
            audio.addEventListener('timeupdate', () => update(box, audio));
            audio.addEventListener('ended', () => {
                box.classList.remove('is-playing');
                audio.currentTime = 0;
                update(box, audio);
            });
            current = { box, audio };
        }
        const { audio } = current;
        const wave = event.target.closest('[data-dm-voice-wave]');
        if (wave) {
            // Tap the waveform to jump.
            const rect = wave.getBoundingClientRect();
            const duration = Number(box.dataset.duration) || audio.duration || 0;
            if (duration) audio.currentTime = ((event.clientX - rect.left) / rect.width) * duration;
            if (audio.paused) { audio.play().catch(() => {}); box.classList.add('is-playing'); }
            update(box, audio);
            return;
        }
        if (audio.paused) {
            audio.play().then(() => box.classList.add('is-playing')).catch(() => window.bookwormToast?.('That voice message couldn’t be played.', 'error'));
        } else {
            audio.pause();
            box.classList.remove('is-playing');
        }
    });
}

/* ---------------- photo viewer ---------------- */

function initViewer() {
    let viewer;
    let gallery = [];
    let index = 0;

    const show = () => {
        viewer.querySelector('img').src = gallery[index];
        viewer.querySelector('[data-prev]').hidden = index === 0;
        viewer.querySelector('[data-next]').hidden = index >= gallery.length - 1;
    };

    document.addEventListener('click', (event) => {
        const item = event.target.closest('[data-dm-view]');
        if (!item) return;
        if (!viewer) {
            viewer = document.createElement('dialog');
            viewer.className = 'dm-viewer';
            viewer.setAttribute('aria-label', 'Photo');
            viewer.innerHTML = '<img alt=""><button type="button" data-close aria-label="Close">×</button><button type="button" data-prev aria-label="Previous photo">‹</button><button type="button" data-next aria-label="Next photo">›</button>';
            document.body.append(viewer);
            viewer.querySelector('[data-close]').addEventListener('click', () => viewer.close());
            viewer.querySelector('[data-prev]').addEventListener('click', () => { index = Math.max(0, index - 1); show(); });
            viewer.querySelector('[data-next]').addEventListener('click', () => { index = Math.min(gallery.length - 1, index + 1); show(); });
            viewer.addEventListener('click', (click) => { if (click.target === viewer) viewer.close(); });
            viewer.addEventListener('keydown', (key) => {
                if (key.key === 'ArrowLeft' && index > 0) { index -= 1; show(); }
                if (key.key === 'ArrowRight' && index < gallery.length - 1) { index += 1; show(); }
            });
        }
        gallery = (item.dataset.dmGallery || item.dataset.dmView).split('|');
        index = Number(item.dataset.dmIndex) || 0;
        show();
        viewer.showModal();
    });
}

/* ---------------- the conversation list, kept fresh ---------------- */

function initLiveList(list) {
    const refresh = async () => {
        if (document.hidden) return;
        try {
            const payload = await json(list.dataset.listUrl);
            if (payload.html.trim() !== list.innerHTML.trim()) list.innerHTML = payload.html;
            document.querySelectorAll('[data-dm-requests]').forEach((count) => {
                count.textContent = String(payload.requests);
                count.hidden = payload.requests < 1;
            });
            setBadge(payload.unread);
        } catch {
            // try again next time
        }
    };
    window.setInterval(refresh, 5000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) refresh(); });
}

/* On every other page, check the messages badge now and then. */
function initBadge() {
    const check = async () => {
        if (document.hidden) return;
        try {
            setBadge((await json('/messages/unread')).unread);
        } catch {
            // ignore
        }
    };
    window.setInterval(check, 30000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) check(); });
}

/* ---------------- people lists (pickers) ---------------- */

function personButton(person, { selectable = false } = {}) {
    const button = document.createElement('button');
    button.type = 'button';
    button.className = 'dm-person';
    button.dataset.username = person.username;
    const text = document.createElement('span');
    text.className = 'reader-suggest__text';
    const name = document.createElement('b');
    name.textContent = person.name;
    const handle = document.createElement('small');
    handle.textContent = `@${person.username}`;
    text.append(name, handle);
    button.append(readerAvatar(person), text);
    if (selectable) {
        button.setAttribute('aria-pressed', 'false');
        const check = document.createElement('span');
        check.className = 'dm-person__check';
        check.innerHTML = '<svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12l5 5 9-10"/></svg>';
        button.append(check);
    }
    return button;
}

let contactsCache = null;
const contacts = async () => {
    contactsCache ??= json('/messages/contacts').catch(() => []);
    return contactsCache;
};

/** A search box that shows contacts when empty and live matches while typing. */
function peopleSearch(input, results, render) {
    let timer;
    let controller;
    const show = async () => {
        const query = input.value.trim();
        controller?.abort();
        controller = new AbortController();
        try {
            const people = query ? await fetchReaders(query, controller.signal) : await contacts();
            if (input.value.trim() !== query) return;
            render(people, query);
        } catch {
            // aborted
        }
    };
    input.addEventListener('input', () => {
        window.clearTimeout(timer);
        timer = window.setTimeout(show, 120);
    });
    return show;
}

function emptyNote(text) {
    const note = document.createElement('p');
    note.className = 'px-3 py-8 text-center text-sm text-tan';
    note.textContent = text;
    return note;
}

/* ---------------- "New message" ---------------- */

function initNewMessage(picker) {
    const input = picker.querySelector('[data-dm-picker-input]');
    const results = picker.querySelector('[data-dm-picker-results]');
    const show = peopleSearch(input, results, (people, query) => {
        results.replaceChildren();
        if (!people.length) {
            results.append(emptyNote(query ? 'No readers found.' : 'Search for a reader to message.'));
            return;
        }
        if (!query) {
            const heading = document.createElement('p');
            heading.className = 'bw-palette__heading';
            heading.textContent = 'Suggested';
            results.append(heading);
        }
        people.forEach((person) => {
            const button = personButton(person);
            button.addEventListener('click', () => { window.location.href = `/messages/to/${encodeURIComponent(person.username)}`; });
            results.append(button);
        });
    });

    document.querySelectorAll('[data-dm-new]').forEach((button) => button.addEventListener('click', () => {
        input.value = '';
        picker.showModal();
        input.focus();
        show();
    }));
    picker.querySelector('[data-dm-picker-close]').addEventListener('click', () => picker.close());
    picker.addEventListener('click', (event) => { if (event.target === picker) picker.close(); });
}

/* ---------------- "Send to" for posts ---------------- */

function buildSendSheet() {
    const sheet = document.createElement('dialog');
    sheet.className = 'dm-picker';
    sheet.setAttribute('aria-labelledby', 'send-sheet-title');
    sheet.innerHTML = `
        <header class="flex items-center gap-2 border-b border-warmborder px-3 py-2.5 dark:border-[#4B3035]">
            <button type="button" class="ig-icon !h-9 !w-9" data-close aria-label="Close"><svg viewBox="0 0 24 24" aria-hidden="true"><path d="M6 6l12 12M18 6L6 18"/></svg></button>
            <h2 id="send-sheet-title" class="flex-1 text-center font-semibold">Share</h2>
            <span class="w-9" aria-hidden="true"></span>
        </header>
        <div class="border-b border-warmborder px-4 py-2 dark:border-[#4B3035]">
            <label class="sr-only" for="send-sheet-search">Search readers</label>
            <input id="send-sheet-search" type="search" placeholder="Search…" autocomplete="off" class="w-full border-0 bg-transparent px-0 text-sm focus:ring-0" data-search>
        </div>
        <div class="max-h-72 min-h-40 overflow-y-auto p-2" data-results></div>
        <div class="space-y-2 border-t border-warmborder p-3 dark:border-[#4B3035]">
            <label class="sr-only" for="send-sheet-note">Write a message</label>
            <input id="send-sheet-note" type="text" maxlength="2000" placeholder="Write a message…" class="bw-input w-full text-sm" data-note hidden>
            <div class="flex gap-2">
                <button type="button" class="btn-ghost min-h-0 flex-1 py-2" data-copy>Copy link</button>
                <button type="button" class="btn-primary min-h-0 flex-1 py-2" data-send disabled>Send</button>
            </div>
        </div>`;
    document.body.append(sheet);
    return sheet;
}

function initSendSheet() {
    let sheet;
    let current = null; // { sendUrl, shareUrl }
    let chosen = new Map();

    const open = (button) => {
        sheet ??= buildSendSheet();
        const search = sheet.querySelector('[data-search]');
        const results = sheet.querySelector('[data-results]');
        const note = sheet.querySelector('[data-note]');
        const sendButton = sheet.querySelector('[data-send]');
        current = { sendUrl: button.dataset.sendPost, shareUrl: new URL(button.dataset.shareUrl, window.location.origin).toString() };
        chosen = new Map();

        const refresh = () => {
            sendButton.disabled = chosen.size === 0;
            sendButton.textContent = chosen.size > 1 ? `Send separately (${chosen.size})` : 'Send';
            note.hidden = chosen.size === 0;
        };

        const show = peopleSearch(search, results, (people, query) => {
            results.replaceChildren();
            if (!people.length) {
                results.append(emptyNote(query ? 'No readers found.' : 'Follow readers to share posts with them.'));
                return;
            }
            people.forEach((person) => {
                const button = personButton(person, { selectable: true });
                button.setAttribute('aria-pressed', String(chosen.has(person.username)));
                button.addEventListener('click', () => {
                    if (chosen.has(person.username)) chosen.delete(person.username); else chosen.set(person.username, person);
                    button.setAttribute('aria-pressed', String(chosen.has(person.username)));
                    refresh();
                });
                results.append(button);
            });
        });

        if (!sheet.dataset.ready) {
            sheet.dataset.ready = '1';
            sheet.querySelector('[data-close]').addEventListener('click', () => sheet.close());
            sheet.addEventListener('click', (event) => { if (event.target === sheet) sheet.close(); });
            sheet.querySelector('[data-copy]').addEventListener('click', async () => {
                try {
                    await navigator.clipboard.writeText(current.shareUrl);
                    window.bookwormToast?.('Link copied.');
                } catch {
                    window.prompt('Copy this link', current.shareUrl);
                }
                sheet.close();
            });
            sendButton.addEventListener('click', async () => {
                if (!chosen.size) return;
                sendButton.disabled = true;
                try {
                    await json(current.sendUrl, {
                        method: 'POST',
                        body: JSON.stringify({ recipients: [...chosen.keys()], body: note.value.trim() || null }),
                    });
                    const names = [...chosen.values()].map((person) => person.name.split(' ')[0]);
                    window.bookwormToast?.(`Sent to ${names.length > 2 ? `${names.slice(0, 2).join(', ')} and ${names.length - 2} more` : names.join(' and ')}.`);
                    sheet.close();
                } catch (error) {
                    window.bookwormToast?.(error.message, 'error');
                    sendButton.disabled = false;
                }
            });
        }

        search.value = '';
        note.value = '';
        refresh();
        sheet.showModal();
        show();
    };

    document.addEventListener('click', (event) => {
        const button = event.target.closest('[data-send-post]');
        if (!button) return;
        event.preventDefault();
        event.stopImmediatePropagation();
        open(button);
    }, true);
}

export function initMessages() {
    const form = document.querySelector('[data-dm-form]');
    if (form) initChat(form);
    const list = document.querySelector('[data-dm-list]');
    if (list) initLiveList(list);
    else if (document.querySelector('[data-dm-badge]')) initBadge();
    initVoicePlayers();
    initViewer();
    const picker = document.querySelector('[data-dm-picker]');
    if (picker) initNewMessage(picker);
    initSendSheet();
}
