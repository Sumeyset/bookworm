// Direct messages: the chat (send without reloading, new messages appear on
// their own), the "New message" picker, and the "Send to" sheet for posts.
import { fetchReaders, readerAvatar } from './reader-search.js';

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

function initChat(form) {
    const scroller = document.querySelector('[data-dm-scroll]');
    const list = document.querySelector('[data-dm-messages]');
    const input = form.querySelector('[data-dm-input]');
    const sendButton = form.querySelector('[data-dm-send]');
    const me = form.dataset.me;
    let sending = false;

    const lastId = () => Math.max(0, ...[...list.querySelectorAll('[data-message-id]')].map((row) => Number(row.dataset.messageId)));
    const nearBottom = () => scroller.scrollHeight - scroller.scrollTop - scroller.clientHeight < 120;
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

    const append = (html) => {
        const template = document.createElement('template');
        template.innerHTML = html;
        [...template.content.children].forEach((row) => {
            if (!list.querySelector(`[data-message-id="${row.dataset.messageId}"]`)) list.append(row);
        });
        group();
    };

    const autosize = () => {
        input.style.height = 'auto';
        input.style.height = `${Math.min(input.scrollHeight, 128)}px`;
        sendButton.disabled = !input.value.trim() || sending;
    };

    async function send(event) {
        event?.preventDefault();
        const body = input.value.trim();
        if (!body || sending) return;
        sending = true;
        sendButton.disabled = true;
        input.value = '';
        autosize();
        try {
            const payload = await json(form.action, { method: 'POST', body: JSON.stringify({ body }) });
            append(payload.html);
            toBottom();
        } catch (error) {
            input.value = body;
            window.bookwormToast?.(error.message, 'error');
        } finally {
            sending = false;
            autosize();
            input.focus();
        }
    }

    async function poll() {
        if (document.hidden) return;
        try {
            const stick = nearBottom();
            const payload = await json(`${form.dataset.pollUrl}?after=${lastId()}`);
            if (payload.html) {
                append(payload.html);
                if (stick) toBottom();
            }
            setBadge(payload.unread);
        } catch {
            // Offline for a moment: try again next time.
        }
    }

    group();
    toBottom();
    window.addEventListener('load', toBottom, { once: true });
    input.addEventListener('input', autosize);
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) send(event);
    });
    form.addEventListener('submit', send);
    window.setInterval(poll, 4000);
    document.addEventListener('visibilitychange', () => { if (!document.hidden) poll(); });
    if (window.matchMedia('(pointer: fine)').matches) input.focus();
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
    const picker = document.querySelector('[data-dm-picker]');
    if (picker) initNewMessage(picker);
    initSendSheet();
}
