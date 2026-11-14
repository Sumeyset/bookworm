import Alpine from 'alpinejs';

window.Alpine = Alpine;

const themeToggles = document.querySelectorAll('[data-theme-toggle]');
const themePreference = window.matchMedia('(prefers-color-scheme: dark)');

const setTheme = (dark, persist = false) => {
    document.documentElement.classList.toggle('dark', dark);

    if (persist) {
        localStorage.setItem('bookworm-theme', dark ? 'dark' : 'light');
    }

    themeToggles.forEach((toggle) => {
        toggle.setAttribute('aria-pressed', String(dark));
        toggle.setAttribute('aria-label', dark ? 'Switch to light theme' : 'Switch to dark theme');
    });
};

themeToggles.forEach((toggle) => {
    toggle.addEventListener('click', () => {
        setTheme(!document.documentElement.classList.contains('dark'), true);
    });
});

themePreference.addEventListener('change', (event) => {
    if (!localStorage.getItem('bookworm-theme')) {
        setTheme(event.matches);
    }
});

setTheme(document.documentElement.classList.contains('dark'));

// Small toast helper used by the bookshelf tree and flash messages.
window.bookwormToast = (message, tone = 'success') => {
    let stack = document.querySelector('.toast-stack');
    if (!stack) {
        stack = document.createElement('div');
        stack.className = 'toast-stack';
        stack.setAttribute('role', 'status');
        stack.setAttribute('aria-live', 'polite');
        document.body.appendChild(stack);
    }
    const toast = document.createElement('div');
    toast.className = `toast${tone === 'error' ? ' toast--error' : ''}`;
    toast.textContent = message;
    stack.appendChild(toast);
    window.setTimeout(() => {
        toast.classList.add('is-leaving');
        toast.addEventListener('animationend', () => toast.remove(), { once: true });
    }, tone === 'error' ? 5000 : 3200);
};

document.querySelectorAll('[data-flash]').forEach((flash) => {
    window.bookwormToast(flash.dataset.flash, flash.dataset.tone);
});

// Search palette: Ctrl/⌘+K or "/" opens it; type to filter quick links or search books.
const palette = document.getElementById('command-palette');
if (palette) {
    const input = palette.querySelector('[data-palette-input]');
    const form = palette.querySelector('[data-palette-form]');
    const searchRow = palette.querySelector('[data-palette-search]');
    const queryLabel = palette.querySelector('[data-palette-query]');
    const entries = [...palette.querySelectorAll('[data-palette-entry]')];
    const isMac = /Mac|iPhone|iPad/.test(navigator.platform);
    document.querySelectorAll('[data-kbd-hint]').forEach((hint) => { hint.textContent = isMac ? '⌘K' : 'Ctrl K'; });

    const items = () => [...palette.querySelectorAll('.bw-palette__item')].filter((item) => !item.closest('[hidden]'));
    let active = 0;
    const highlight = (index) => {
        const list = items();
        active = (index + list.length) % Math.max(1, list.length);
        list.forEach((item, i) => item.classList.toggle('is-active', i === active));
        list[active]?.scrollIntoView({ block: 'nearest' });
    };
    const filter = () => {
        const query = input.value.trim().toLowerCase();
        entries.forEach((entry) => { entry.hidden = query !== '' && !entry.dataset.keywords.includes(query); });
        searchRow.hidden = query === '';
        queryLabel.textContent = input.value.trim();
        highlight(0);
    };
    const open = () => {
        if (palette.open) return;
        input.value = '';
        filter();
        palette.showModal();
        input.focus();
    };

    document.querySelectorAll('[data-open-palette]').forEach((button) => button.addEventListener('click', open));
    document.addEventListener('keydown', (event) => {
        const typing = /INPUT|TEXTAREA|SELECT/.test(document.activeElement?.tagName) || document.activeElement?.isContentEditable;
        if ((event.key === 'k' && (event.metaKey || event.ctrlKey)) || (event.key === '/' && !typing)) {
            event.preventDefault();
            open();
        }
    });
    input.addEventListener('input', filter);
    input.addEventListener('keydown', (event) => {
        if (event.key === 'ArrowDown') { event.preventDefault(); highlight(active + 1); }
        if (event.key === 'ArrowUp') { event.preventDefault(); highlight(active - 1); }
        if (event.key === 'Enter') {
            const target = items()[active];
            if (target && !target.hasAttribute('data-palette-submit')) { event.preventDefault(); target.click(); }
        }
    });
    palette.querySelector('[data-palette-submit]').addEventListener('click', () => form.requestSubmit());
    palette.addEventListener('click', (event) => { if (event.target === palette) palette.close(); });
}

// Quote cards: copy share links and download a quote as an image card.
const quoteXmlEscape = (value) => String(value).replace(/[<>&'"]/g, (character) => ({
    '<': '&lt;', '>': '&gt;', '&': '&amp;', "'": '&apos;', '"': '&quot;',
})[character]);

document.querySelectorAll('[data-copy-link]').forEach((button) => {
    button.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText(button.dataset.copyLink);
            button.textContent = 'Copied';
        } catch {
            window.prompt('Copy your quote link', button.dataset.copyLink);
        }
    });
});

document.querySelectorAll('[data-download-quote]').forEach((button) => {
    button.addEventListener('click', () => {
        const text = quoteXmlEscape(button.dataset.quote);
        const title = quoteXmlEscape(button.dataset.title);
        const attribution = quoteXmlEscape(`${button.dataset.author || 'Unknown author'}${button.dataset.page ? ` · Page ${button.dataset.page}` : ''}`);
        const reader = quoteXmlEscape(button.dataset.reader);
        const page = button.dataset.page ? ` · Page ${button.dataset.page}` : '';
        const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="1200" height="800" viewBox="0 0 1200 800"><rect width="1200" height="800" fill="#F7F1EA"/><rect x="34" y="34" width="1132" height="732" rx="8" fill="#FFFDF9" stroke="#E6D9D0" stroke-width="2"/><text x="100" y="190" font-family="Georgia,serif" font-size="110" fill="#6D1A2B">“</text><foreignObject x="140" y="180" width="900" height="340"><div xmlns="http://www.w3.org/1999/xhtml" style="font:italic 48px/1.45 Georgia,serif;color:#2A1A1D">${text}</div></foreignObject><text x="145" y="610" font-family="Georgia,serif" font-size="30" fill="#6D1A2B">${title}</text><text x="145" y="665" font-family="Arial,sans-serif" font-size="22" fill="#8A6A55">${attribution}</text><text x="145" y="720" font-family="Arial,sans-serif" font-size="18" fill="#8A6A55">Kept by ${reader} · Bookworm</text></svg>`;
        const url = URL.createObjectURL(new Blob([svg], { type: 'image/svg+xml;charset=utf-8' }));
        const link = document.createElement('a');
        link.href = url;
        link.download = 'bookworm-quote.svg';
        link.click();
        window.setTimeout(() => URL.revokeObjectURL(url), 1000);
    });
});

// Year in Reading: download a recap card.
document.querySelectorAll('[data-recap-card]').forEach((button) => {
    button.addEventListener('click', () => {
        const d = button.dataset;
        const esc = (value) => String(value ?? '').replace(/[<>&'"]/g, (c) => ({ '<': '&lt;', '>': '&gt;', '&': '&amp;', "'": '&apos;', '"': '&quot;' })[c]);
        const stat = (x, value, label) => `<text x="${x}" y="560" font-family="Caveat, cursive" font-weight="700" font-size="120" fill="#FFF8EF">${esc(value)}</text><text x="${x + 4}" y="610" font-family="Arial, sans-serif" font-size="24" fill="#F2C38B" letter-spacing="3">${label}</text>`;
        const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="1080" height="1080" viewBox="0 0 1080 1080"><defs><linearGradient id="g" x1="0" y1="0" x2="1" y2="1"><stop offset="0" stop-color="#2a0d15"/><stop offset=".5" stop-color="#6D1A2B"/><stop offset="1" stop-color="#8B2A3D"/></linearGradient></defs><rect width="1080" height="1080" fill="url(#g)"/><rect x="40" y="40" width="1000" height="1000" rx="40" fill="none" stroke="#C9A273" stroke-opacity=".5" stroke-width="2"/><text x="100" y="170" font-family="Arial, sans-serif" font-size="26" letter-spacing="8" fill="#F2C38B">YEAR IN READING</text><text x="96" y="330" font-family="Caveat, cursive" font-weight="700" font-size="150" fill="#FFF8EF">${esc(d.name.split(' ')[0])}’s ${esc(d.year)}</text>${stat(100, d.books, 'BOOKS')}${stat(420, d.pages, 'PAGES')}${stat(780, d.days, 'DAYS')}${d.top ? `<text x="100" y="770" font-family="Arial, sans-serif" font-size="24" letter-spacing="4" fill="#F2C38B">FAVOURITE</text><text x="98" y="850" font-family="Caveat, cursive" font-weight="700" font-size="72" fill="#FFF8EF">${esc(d.top.slice(0, 32))}</text>` : ''}<text x="100" y="980" font-family="Georgia, serif" font-size="32" font-weight="700" fill="#FFF8EF">Book<tspan fill="#F4A9B8">worm</tspan></text><text x="980" y="980" text-anchor="end" font-family="Arial, sans-serif" font-size="22" fill="#F1D9C4">longest streak · ${esc(d.streak)} days</text></svg>`;
        const url = URL.createObjectURL(new Blob([svg], { type: 'image/svg+xml;charset=utf-8' }));
        const link = document.createElement('a');
        link.href = url;
        link.download = `bookworm-${d.year}-in-books.svg`;
        link.click();
        window.setTimeout(() => URL.revokeObjectURL(url), 1000);
    });
});

// Covers that fail to load turn into a cloth cover with the title.
document.addEventListener('error', (event) => {
    const img = event.target;
    if (!(img instanceof HTMLImageElement) || !img.hasAttribute('data-cover-fallback')) return;
    const span = document.createElement('span');
    span.className = `${img.className} flex items-center justify-center bg-gradient-to-br from-burgundy via-[#8B2A3D] to-burgundy-deep p-1.5 text-center font-serif text-[10px] leading-tight text-[#FFF8EF]`;
    span.textContent = img.dataset.coverFallback;
    img.replaceWith(span);
}, true);

// Likes on posts and reviews (no page reload). Works for the old pill buttons
// (count inside) and the Instagram-style heart (count in "N likes" below).
const setLikeCount = (button, count) => {
    const inner = button.querySelector('[data-like-count]');
    if (inner) inner.textContent = String(count);
    if (!button.dataset.likeKey) return;
    document.querySelectorAll(`[data-like-total="${button.dataset.likeKey}"]`).forEach((total) => {
        total.dataset.count = String(count);
        total.textContent = `${count} ${count === 1 ? 'like' : 'likes'}`;
    });
};
const likeCount = (button) => Number(
    button.querySelector('[data-like-count]')?.textContent
    ?? document.querySelector(`[data-like-total="${button.dataset.likeKey}"]`)?.dataset.count
    ?? 0,
);

async function toggleLike(button) {
    const liked = button.getAttribute('aria-pressed') !== 'true';
    const before = likeCount(button);
    button.setAttribute('aria-pressed', String(liked));
    setLikeCount(button, Math.max(0, before + (liked ? 1 : -1)));
    button.classList.add('is-popping');
    window.setTimeout(() => button.classList.remove('is-popping'), 400);
    try {
        const response = await fetch(button.dataset.likeUrl, {
            method: 'POST',
            credentials: 'same-origin',
            headers: { Accept: 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content ?? '' },
        });
        if (!response.ok) throw new Error();
        const data = await response.json();
        button.setAttribute('aria-pressed', String(data.liked));
        setLikeCount(button, data.count);
    } catch {
        button.setAttribute('aria-pressed', String(!liked));
        setLikeCount(button, before);
        window.bookwormToast?.('Couldn’t save that like. Please try again.', 'error');
    }
}

document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-like-url]');
    if (!button) return;
    event.preventDefault();
    toggleLike(button);
});

// Double-tap (or double-click) a post's picture to like it, with a heart burst.
const burst = (media) => {
    const heart = media.querySelector('.ig-heart-burst');
    if (!heart) return;
    heart.classList.remove('is-on');
    void heart.getBoundingClientRect();
    heart.classList.add('is-on');
};
const likeFromMedia = (media) => {
    burst(media);
    const button = media.closest('article')?.querySelector('[data-like-url]');
    if (button && button.getAttribute('aria-pressed') !== 'true') toggleLike(button);
};
let lastTap = { target: null, time: 0 };
document.addEventListener('dblclick', (event) => {
    const media = event.target.closest('[data-double-like]');
    if (media) likeFromMedia(media);
});
document.addEventListener('pointerup', (event) => {
    if (event.pointerType === 'mouse') return;
    const media = event.target.closest('[data-double-like]');
    if (!media) return;
    const now = Date.now();
    if (lastTap.target === media && now - lastTap.time < 320) {
        likeFromMedia(media);
        lastTap = { target: null, time: 0 };
    } else {
        lastTap = { target: media, time: now };
    }
});

// Share a post: the phone's share sheet when there is one, otherwise copy the link.
document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-share-url]');
    if (!button) return;
    event.preventDefault();
    const url = new URL(button.dataset.shareUrl, window.location.origin).toString();
    if (navigator.share && window.matchMedia('(pointer: coarse)').matches) {
        try {
            await navigator.share({ title: button.dataset.shareTitle || 'Bookworm', url });
            return;
        } catch (error) {
            if (error?.name === 'AbortError') return;
        }
    }
    try {
        await navigator.clipboard.writeText(url);
        window.bookwormToast?.('Link copied.');
    } catch {
        window.prompt('Copy this link', url);
    }
});

// Open the "Create new post" flow from any [data-open-composer] button.
document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-open-composer]');
    if (!button) return;
    event.preventDefault();
    window.dispatchEvent(new CustomEvent('open-composer', {
        detail: { kind: button.dataset.openComposer, cardId: Number(button.dataset.cardId) || null },
    }));
});

const lengthClass = (text) => {
    const length = [...String(text)].length;
    if (length <= 80) return 's';
    if (length <= 180) return 'm';
    if (length <= 320) return 'l';
    if (length <= 520) return 'xl';
    return 'xxl';
};

// The Instagram-style composer: choose → write → design & caption → share.
Alpine.data('composer', ({ cards = [], books = [], old = null } = {}) => ({
    cards,
    books,
    isOpen: false,
    step: 1,
    kind: 'quote',
    source: 'typed',
    quoteText: '',
    cardId: null,
    bookId: null,
    page: '',
    caption: '',
    thought: '',
    onCard: false,
    design: 'wine',
    font: 'serif',
    submitting: false,

    init() {
        if (old) {
            this.kind = old.kind;
            this.source = old.cardId ? 'saved' : 'typed';
            this.quoteText = old.quoteText || '';
            this.cardId = old.cardId;
            this.bookId = old.bookId;
            this.page = old.page || '';
            if (old.kind === 'quote') this.caption = old.body || ''; else this.thought = old.body || '';
            this.onCard = Boolean(old.design);
            this.design = old.design || 'wine';
            this.font = old.font || (old.kind === 'quote' ? 'serif' : 'sans');
            this.isOpen = true;
            this.step = 2;
        }
        const params = new URLSearchParams(window.location.search);
        if (!old && params.get('compose')) {
            this.open({ kind: params.get('compose') === 'quote' ? 'quote' : 'thought', cardId: Number(params.get('card')) || null });
        }
        this.$watch('isOpen', (open) => document.documentElement.classList.toggle('overflow-hidden', open));
    },

    open({ kind = null, cardId = null } = {}) {
        this.isOpen = true;
        this.submitting = false;
        if (cardId && this.cards.some((card) => card.id === cardId)) {
            this.kind = 'quote';
            this.source = 'saved';
            this.cardId = cardId;
            this.design = 'wine';
            this.font = 'serif';
            this.step = 3;
            return;
        }
        if (kind) this.choose(kind); else this.step = 1;
    },

    close() {
        if (!this.isOpen) return;
        this.isOpen = false;
    },

    choose(kind) {
        this.kind = kind;
        if (kind === 'thought') {
            this.font = 'sans';
            this.design = this.design === 'wine' ? 'sunset' : this.design;
        } else {
            this.font = 'serif';
        }
        this.step = 2;
        this.$nextTick(() => (kind === 'quote' ? this.$refs.quote : this.$refs.thought)?.focus());
    },

    canContinue() {
        if (this.kind === 'thought') return this.thought.trim().length > 0;
        return this.source === 'saved' ? Boolean(this.cardId) : this.quoteText.trim().length > 0;
    },

    next() {
        if (this.canContinue()) this.step = 3;
    },

    selectedCard() {
        return this.cards.find((card) => card.id === this.cardId) || null;
    },

    previewText() {
        if (this.kind === 'thought') return this.thought.trim();
        return this.source === 'saved' ? (this.selectedCard()?.text ?? '') : this.quoteText.trim();
    },

    credit() {
        if (this.source === 'saved') {
            const card = this.selectedCard();
            return { title: card?.title ?? '', meta: [card?.author, card?.page ? `p. ${card.page}` : null].filter(Boolean).join(' · ') };
        }
        const book = this.books.find((entry) => entry.id === this.bookId);
        return { title: book?.title ?? '', meta: [book?.author, this.page ? `p. ${this.page}` : null].filter(Boolean).join(' · ') };
    },

    cardClasses() {
        return `qcard--${this.design} qcard--f-${this.font} qcard--len-${lengthClass(this.previewText())}${this.kind === 'thought' ? ' qcard--thought' : ''}`;
    },
}));

Alpine.start();
