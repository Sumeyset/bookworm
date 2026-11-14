// Barnaby the worm: the chat bubble, plus helpers the reader and buddy reads use.

const csrf = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

/** Ask Barnaby something. Always resolves with { reply, ... } (errors come back as a friendly reply). */
export async function askBarnaby(url, body = {}) {
    try {
        const response = await fetch(url, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf() },
            body: JSON.stringify(body),
        });
        const payload = await response.json().catch(() => ({}));
        if (!response.ok) {
            const message = payload.reply
                || (payload.errors ? Object.values(payload.errors).flat().join(' ') : null)
                || 'Barnaby couldn’t answer just now. Please try again.';
            return { reply: message, failed: true };
        }
        return payload;
    } catch {
        return { reply: 'Barnaby can’t reach the internet right now. Check your connection and try again.', failed: true };
    }
}

const escapeHtml = (text) => String(text).replace(/[&<>"']/g, (character) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' })[character]);

/** Barnaby writes a little Markdown (bold, italics, lists); turn it into safe HTML. */
export function renderBarnaby(text) {
    const inline = (line) => escapeHtml(line)
        .replace(/\*\*(.+?)\*\*/g, '<b>$1</b>')
        .replace(/(^|[^*])\*(?!\s)(.+?)\*(?!\*)/g, '$1<i>$2</i>')
        .replace(/`([^`]+)`/g, '<code>$1</code>');

    const blocks = [];
    let list = null;
    String(text).replace(/\r/g, '').split('\n').forEach((raw) => {
        const line = raw.trim();
        const bullet = line.match(/^[-*•]\s+(.*)$/);
        const numbered = line.match(/^\d+[.)]\s+(.*)$/);
        if (bullet || numbered) {
            const type = bullet ? 'ul' : 'ol';
            if (!list || list.type !== type) {
                list = { type, items: [] };
                blocks.push(list);
            }
            list.items.push(inline((bullet || numbered)[1]));
            return;
        }
        list = null;
        if (!line) {
            blocks.push(null);
            return;
        }
        const last = blocks[blocks.length - 1];
        if (last && last.type === 'p') last.lines.push(inline(line)); else blocks.push({ type: 'p', lines: [inline(line)] });
    });

    return blocks.filter(Boolean).map((block) => (block.type === 'p'
        ? `<p>${block.lines.join('<br>')}</p>`
        : `<${block.type}>${block.items.map((item) => `<li>${item}</li>`).join('')}</${block.type}>`)).join('');
}

/** Barnaby's avatar, copied from the one on the page (so it's drawn the same everywhere). */
function avatar(className) {
    const source = document.querySelector('.barnaby-fab__avatar, [data-barnaby-avatar]');
    const svg = source ? source.cloneNode(true) : document.createElement('span');
    svg.removeAttribute('data-barnaby-avatar');
    svg.setAttribute('class', className);
    return svg;
}

export function typingIndicator(label = 'Barnaby is reading…') {
    const typing = document.createElement('span');
    typing.className = 'barnaby-typing';
    typing.innerHTML = `<span><i></i> <i></i> <i></i></span><span>${escapeHtml(label)}</span>`;
    return typing;
}

function bookCards(books) {
    const row = document.createElement('div');
    row.className = 'barnaby-books';
    books.forEach((book) => {
        const card = document.createElement('a');
        card.className = 'barnaby-book';
        card.href = book.url;
        const top = document.createElement('div');
        top.className = 'barnaby-book__top';
        let cover;
        if (book.cover) {
            cover = document.createElement('img');
            cover.src = book.cover;
            cover.alt = '';
            cover.loading = 'lazy';
            cover.className = 'barnaby-book__cover';
        } else {
            cover = document.createElement('span');
            cover.className = 'barnaby-book__cover barnaby-book__cover--blank';
            cover.textContent = book.title;
        }
        const heading = document.createElement('div');
        heading.innerHTML = `<p class="barnaby-book__title">${escapeHtml(book.title)}</p>${book.author ? `<p class="barnaby-book__author">${escapeHtml(book.author)}${book.year ? ` · ${book.year}` : ''}</p>` : ''}`;
        top.append(cover, heading);
        const why = document.createElement('p');
        why.className = 'barnaby-book__why';
        why.textContent = book.why || '';
        const cta = document.createElement('span');
        cta.className = 'barnaby-book__cta';
        cta.textContent = book.on_shelf ? 'On your shelf ✓' : 'Open book →';
        card.append(top, why, cta);
        row.append(card);
    });
    return row;
}

/* ---------------- the chat bubble ---------------- */

function initChat(root) {
    const fab = root.querySelector('[data-barnaby-open]');
    const panel = root.querySelector('[data-barnaby-panel]');
    const scroller = root.querySelector('[data-barnaby-scroll]');
    const log = root.querySelector('[data-barnaby-log]');
    const chips = root.querySelector('[data-barnaby-chips]');
    const form = root.querySelector('[data-barnaby-form]');
    const input = root.querySelector('[data-barnaby-input]');
    const sendButton = root.querySelector('[data-barnaby-send]');
    const remaining = root.querySelector('[data-barnaby-remaining]');
    const contextChip = root.querySelector('[data-barnaby-context-ask]');
    const storageKey = `bookworm-barnaby-${root.dataset.user}`;
    let history = [];
    let busy = false;

    try {
        history = JSON.parse(sessionStorage.getItem(storageKey) || '[]');
    } catch {
        history = [];
    }
    const save = () => {
        try {
            sessionStorage.setItem(storageKey, JSON.stringify(history.slice(-30)));
        } catch {
            // storage blocked
        }
    };

    // What the reader is looking at right now (a book page or the PDF reader).
    const context = () => window.bookwormBarnabyContext || {};

    const toBottom = () => { scroller.scrollTop = scroller.scrollHeight; };

    const addMessage = (role, text, books = [], muted = false) => {
        const row = document.createElement('div');
        row.className = `barnaby-msg barnaby-msg--${role === 'user' ? 'me' : 'bot'}${muted ? ' barnaby-msg--muted' : ''}`;
        const bubble = document.createElement('div');
        bubble.className = 'barnaby-msg__bubble';
        if (role === 'user') {
            bubble.textContent = text;
        } else {
            row.append(avatar('barnaby-msg__avatar'));
            bubble.innerHTML = renderBarnaby(text);
        }
        row.append(bubble);
        log.append(row);
        if (books.length) log.append(bookCards(books));
        return row;
    };

    const render = () => {
        log.replaceChildren();
        history.forEach((turn) => addMessage(turn.role, turn.text, turn.books || [], turn.muted));
        chips.hidden = history.length > 0;
        toBottom();
    };

    const refreshContextChip = () => {
        const { title } = context();
        contextChip.hidden = !title;
        if (title) {
            contextChip.textContent = `🪱 Is “${title.length > 28 ? `${title.slice(0, 27)}…` : title}” for me?`;
            contextChip.dataset.barnabyAsk = `Would I enjoy “${title}”? Tell me a little about it, spoiler-free, and how it fits my shelf.`;
        }
    };

    const autosize = () => {
        input.style.height = 'auto';
        input.style.height = `${Math.min(input.scrollHeight, 112)}px`;
        sendButton.disabled = busy || !input.value.trim();
    };

    async function ask(message) {
        message = message.trim();
        if (!message || busy) return;
        busy = true;
        chips.hidden = true;
        const previous = history.filter((turn) => !turn.muted).map(({ role, text }) => ({ role, text }));
        history.push({ role: 'user', text: message });
        addMessage('user', message);
        input.value = '';
        autosize();

        const waiting = document.createElement('div');
        waiting.className = 'barnaby-msg barnaby-msg--bot';
        waiting.append(avatar('barnaby-msg__avatar'), typingIndicator());
        log.append(waiting);
        toBottom();

        const { book_id: bookId, page } = context();
        const result = await askBarnaby(root.dataset.chatUrl, {
            message,
            history: previous.slice(-10),
            ...(bookId ? { book_id: bookId } : {}),
            ...(page ? { page } : {}),
        });
        waiting.remove();

        const turn = { role: 'assistant', text: result.reply, books: result.books || [], muted: Boolean(result.failed) };
        history.push(turn);
        save();
        addMessage('assistant', turn.text, turn.books, turn.muted);
        if (typeof result.remaining === 'number') remaining.textContent = `${result.remaining} questions left today`;
        busy = false;
        autosize();
        toBottom();
        input.focus();
    }

    const open = () => {
        panel.hidden = false;
        fab.setAttribute('aria-expanded', 'true');
        refreshContextChip();
        render();
        input.focus();
    };
    const close = () => {
        panel.hidden = true;
        fab.setAttribute('aria-expanded', 'false');
        fab.focus();
    };

    fab.addEventListener('click', open);
    root.querySelector('[data-barnaby-close]').addEventListener('click', close);
    root.querySelector('[data-barnaby-clear]').addEventListener('click', () => {
        history = [];
        save();
        render();
        input.focus();
    });
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && !panel.hidden) close();
    });
    chips.addEventListener('click', (event) => {
        const chip = event.target.closest('button');
        if (!chip) return;
        if (chip.dataset.barnabyPrefill) {
            input.value = chip.dataset.barnabyPrefill;
            autosize();
            input.focus();
            input.setSelectionRange(input.value.length, input.value.length);
        } else if (chip.dataset.barnabyAsk) {
            ask(chip.dataset.barnabyAsk);
        }
    });
    input.addEventListener('input', autosize);
    input.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && !event.shiftKey && !event.isComposing) {
            event.preventDefault();
            ask(input.value);
        }
    });
    form.addEventListener('submit', (event) => {
        event.preventDefault();
        ask(input.value);
    });

    // Other parts of the site can open Barnaby with a question: [data-ask-barnaby="..."].
    document.addEventListener('click', (event) => {
        const trigger = event.target.closest('[data-ask-barnaby]');
        if (!trigger) return;
        event.preventDefault();
        open();
        if (trigger.dataset.askBarnaby) ask(trigger.dataset.askBarnaby);
    });
}

/* ---------------- buddy reads: discussion questions ---------------- */

function initDiscussion(button) {
    const box = document.querySelector('[data-barnaby-discussion-box]');
    const text = box.querySelector('[data-barnaby-discussion-text]');
    const note = box.querySelector('[data-barnaby-discussion-note]');
    const message = document.getElementById('buddy-message');

    button.addEventListener('click', async () => {
        button.disabled = true;
        box.hidden = false;
        text.replaceChildren(typingIndicator('Barnaby is thinking of good questions…'));
        note.textContent = '';
        const result = await askBarnaby(button.dataset.barnabyDiscussion);
        text.innerHTML = renderBarnaby(result.reply || '');
        button.disabled = false;
        if (result.failed) return;
        note.textContent = result.page
            ? `Spoiler-safe for everyone: nothing past page ${result.page}, where the group’s slowest reader is. Tap a question to ask the group.`
            : 'Tap a question to ask the group.';
        // Tap a question to drop it into the message box.
        text.querySelectorAll('li').forEach((item) => {
            item.style.cursor = 'pointer';
            item.title = 'Ask the group';
            item.addEventListener('click', () => {
                if (!message) return;
                message.value = item.textContent.trim();
                message.dispatchEvent(new Event('input'));
                message.focus();
                message.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        });
    });
}

export function initBarnaby() {
    const root = document.querySelector('[data-barnaby]');
    if (root) initChat(root);
    const discussion = document.querySelector('[data-barnaby-discussion]');
    if (discussion) initDiscussion(discussion);
}
