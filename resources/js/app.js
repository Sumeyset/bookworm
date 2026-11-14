import Alpine from 'alpinejs';
import { attachReaderSuggest, fetchReaders, markMatch, readerAvatar, readerMeta } from './reader-search.js';

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

    // Readers matching what's typed (top five), above the page shortcuts.
    const readerGroup = palette.querySelector('[data-palette-readers]');
    let readerController;
    let readerTimer;
    const showReaders = async () => {
        const query = input.value.trim();
        readerController?.abort();
        if (!readerGroup) return;
        if (!query) {
            readerGroup.replaceChildren();
            readerGroup.hidden = true;
            highlight(0);
            return;
        }
        readerController = new AbortController();
        try {
            const readers = await fetchReaders(query, readerController.signal);
            if (input.value.trim() !== query) return;
            readerGroup.replaceChildren();
            if (readers.length) {
                const heading = document.createElement('p');
                heading.className = 'bw-palette__heading';
                heading.textContent = 'Readers';
                readerGroup.append(heading);
            }
            readers.forEach((reader) => {
                const link = document.createElement('a');
                link.href = reader.url;
                link.className = 'bw-palette__item';
                const text = document.createElement('span');
                text.className = 'reader-suggest__text';
                const username = document.createElement('b');
                username.append(markMatch(reader.username, query));
                const meta = document.createElement('small');
                meta.textContent = readerMeta(reader);
                text.append(username, meta);
                link.append(readerAvatar(reader), text);
                readerGroup.append(link);
            });
            readerGroup.hidden = readers.length === 0;
            highlight(0);
        } catch {
            // Aborted or offline: keep the page shortcuts.
        }
    };
    input.addEventListener('input', () => {
        window.clearTimeout(readerTimer);
        readerTimer = window.setTimeout(showReaders, 120);
    });
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
let tapStart = null;
document.addEventListener('pointerdown', (event) => {
    tapStart = { x: event.clientX, y: event.clientY };
});
document.addEventListener('dblclick', (event) => {
    const media = event.target.closest('[data-double-like]');
    if (media) likeFromMedia(media);
});
document.addEventListener('pointerup', (event) => {
    if (event.pointerType === 'mouse') return;
    const media = event.target.closest('[data-double-like]');
    if (!media) return;
    // A swipe through photos isn't a tap.
    if (tapStart && Math.hypot(event.clientX - tapStart.x, event.clientY - tapStart.y) > 12) {
        lastTap = { target: null, time: 0 };
        return;
    }
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

// Shrink a photo before upload: max 2048px on the long side, as JPEG.
// Keeps uploads small (and under PHP's upload limit) and drops location data.
async function preparePhoto(file) {
    const source = await createImageBitmap(file, { imageOrientation: 'from-image' }).catch(() => null);
    if (!source) throw new Error(`“${file.name}” isn’t a photo we can open.`);
    const scale = Math.min(1, 2048 / Math.max(source.width, source.height));
    const width = Math.round(source.width * scale);
    const height = Math.round(source.height * scale);
    const canvas = document.createElement('canvas');
    canvas.width = width;
    canvas.height = height;
    const context = canvas.getContext('2d');
    context.fillStyle = '#ffffff';
    context.fillRect(0, 0, width, height);
    context.drawImage(source, 0, 0, width, height);
    source.close?.();
    const blob = await new Promise((resolve) => canvas.toBlob(resolve, 'image/jpeg', 0.88));
    if (!blob) throw new Error(`“${file.name}” couldn’t be prepared.`);
    const name = `${file.name.replace(/\.[^.]+$/, '') || 'photo'}.jpg`;
    return { file: new File([blob], name, { type: 'image/jpeg' }), width, height };
}

let photoIds = 0;

// The Instagram-style composer.
//   photo:   choose → select → edit (frame & filter) → caption → share
//   quote:   choose → write → design (& caption) → share
//   thought: choose → write → design → share
Alpine.data('composer', ({ cards = [], books = [], old = null, maxPhotos = 10 } = {}) => ({
    cards,
    books,
    maxPhotos,
    isOpen: false,
    stage: 'choose',
    kind: 'photo',
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
    photos: [],
    active: 0,
    ratio: 'square',
    processing: false,
    dragOver: false,
    dragging: null,
    submitting: false,

    stages() {
        return {
            photo: ['choose', 'select', 'edit', 'caption'],
            quote: ['choose', 'write', 'design'],
            thought: ['choose', 'write', 'design'],
        }[this.kind];
    },

    title() {
        return {
            choose: 'Create new post',
            select: 'Choose photos',
            edit: 'Frame & filter',
            caption: 'New post',
            write: this.kind === 'quote' ? 'Your quote' : 'Your thought',
            design: 'Design & share',
        }[this.stage];
    },

    wide() {
        return ['edit', 'design'].includes(this.stage);
    },

    isLast() {
        const stages = this.stages();
        return this.stage === stages[stages.length - 1];
    },

    init() {
        if (old) {
            this.kind = old.kind;
            this.source = old.cardId ? 'saved' : 'typed';
            this.quoteText = old.quoteText || '';
            this.cardId = old.cardId;
            this.bookId = old.bookId;
            this.page = old.page || '';
            if (old.kind === 'thought') this.thought = old.body || ''; else this.caption = old.body || '';
            this.onCard = Boolean(old.design);
            this.design = old.design || 'wine';
            this.font = old.font || (old.kind === 'quote' ? 'serif' : 'sans');
            this.isOpen = true;
            // Photos can't survive a page reload, so a photo post starts again from "choose photos".
            this.stage = old.kind === 'photo' ? 'select' : 'write';
        }
        const params = new URLSearchParams(window.location.search);
        if (!old && params.get('compose')) {
            const kind = ['quote', 'photo'].includes(params.get('compose')) ? params.get('compose') : 'thought';
            this.open({ kind, cardId: Number(params.get('card')) || null });
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
            this.stage = 'design';
            return;
        }
        if (kind) this.choose(kind); else this.stage = 'choose';
    },

    close() {
        if (!this.isOpen) return;
        this.isOpen = false;
    },

    back() {
        if (this.stage === 'choose') {
            this.close();
            return;
        }
        const stages = this.stages();
        this.stage = stages[Math.max(0, stages.indexOf(this.stage) - 1)];
    },

    next() {
        if (!this.canContinue()) return;
        const stages = this.stages();
        this.stage = stages[Math.min(stages.length - 1, stages.indexOf(this.stage) + 1)];
        if (this.stage === 'caption') this.$nextTick(() => this.$refs.photoCaption?.focus());
    },

    choose(kind) {
        this.kind = kind;
        if (kind === 'photo') {
            this.stage = this.photos.length ? 'edit' : 'select';
            if (!this.photos.length) this.$nextTick(() => this.$refs.picker?.click());
            return;
        }
        if (kind === 'thought') {
            this.font = 'sans';
            this.design = this.design === 'wine' ? 'sunset' : this.design;
        } else {
            this.font = 'serif';
        }
        this.stage = 'write';
        this.$nextTick(() => (kind === 'quote' ? this.$refs.quote : this.$refs.thought)?.focus());
    },

    canContinue() {
        if (this.stage === 'select' || this.stage === 'edit') return this.photos.length > 0 && !this.processing;
        if (this.kind === 'thought') return this.thought.trim().length > 0;
        if (this.kind === 'quote') return this.source === 'saved' ? Boolean(this.cardId) : this.quoteText.trim().length > 0;
        return true;
    },

    /* ---------- photos ---------- */

    async addFiles(fileList) {
        const files = [...fileList].filter((file) => file.type.startsWith('image/'));
        if (!files.length) return;
        const room = this.maxPhotos - this.photos.length;
        if (files.length > room) {
            window.bookwormToast?.(`You can add up to ${this.maxPhotos} photos to a post.`, 'error');
        }
        this.processing = true;
        try {
            for (const file of files.slice(0, Math.max(0, room))) {
                try {
                    const prepared = await preparePhoto(file);
                    this.photos.push({
                        id: ++photoIds,
                        file: prepared.file,
                        url: URL.createObjectURL(prepared.file),
                        width: prepared.width,
                        height: prepared.height,
                        focusX: 50,
                        focusY: 50,
                        filter: 'normal',
                    });
                } catch (error) {
                    window.bookwormToast?.(error.message, 'error');
                }
            }
        } finally {
            this.processing = false;
        }
        if (this.photos.length) {
            // A single tall photo suits the portrait frame, a wide one the landscape frame.
            if (this.photos.length === files.length) {
                const first = this.photos[0];
                const shape = first.width / first.height;
                this.ratio = shape < 0.9 ? 'portrait' : shape > 1.5 ? 'landscape' : 'square';
            }
            this.active = Math.min(this.active, this.photos.length - 1);
            if (this.stage === 'select') this.stage = 'edit';
        }
    },

    current() {
        return this.photos[this.active] || null;
    },

    removePhoto(index) {
        const [removed] = this.photos.splice(index, 1);
        if (removed) URL.revokeObjectURL(removed.url);
        this.active = Math.max(0, Math.min(this.active, this.photos.length - 1));
        if (!this.photos.length) this.stage = 'select';
    },

    setFilter(filter) {
        if (this.current()) this.current().filter = filter;
    },

    filterAll() {
        const filter = this.current()?.filter ?? 'normal';
        this.photos.forEach((photo) => { photo.filter = filter; });
    },

    startDrag(event) {
        if (!this.current()) return;
        event.currentTarget.setPointerCapture?.(event.pointerId);
        this.dragging = { x: event.clientX, y: event.clientY, focusX: this.current().focusX, focusY: this.current().focusY };
    },

    drag(event) {
        if (!this.dragging || !this.current()) return;
        const frame = this.$refs.frame.getBoundingClientRect();
        const photo = this.current();
        // How much of the photo overflows the frame in each direction decides how far a drag moves it.
        const cover = Math.max(frame.width / photo.width, frame.height / photo.height);
        const spareX = Math.max(1, photo.width * cover - frame.width);
        const spareY = Math.max(1, photo.height * cover - frame.height);
        photo.focusX = Math.min(100, Math.max(0, this.dragging.focusX - ((event.clientX - this.dragging.x) / spareX) * 100));
        photo.focusY = Math.min(100, Math.max(0, this.dragging.focusY - ((event.clientY - this.dragging.y) / spareY) * 100));
    },

    endDrag() {
        this.dragging = null;
    },

    prepareSubmit(event) {
        if (this.kind === 'photo') {
            if (!this.photos.length || this.processing) {
                event.preventDefault();
                return;
            }
            const transfer = new DataTransfer();
            this.photos.forEach((photo) => transfer.items.add(photo.file));
            this.$refs.upload.files = transfer.files;
        } else {
            this.$refs.upload.value = '';
        }
        this.submitting = true;
    },

    /* ---------- quote & thought cards ---------- */

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

// Photo carousels in the feed: counter, dots and arrows follow the swipe.
document.querySelectorAll('[data-carousel]').forEach((carousel) => {
    const track = carousel.querySelector('[data-carousel-track]');
    const slides = track.children.length;
    if (slides < 2) return;
    const count = carousel.querySelector('[data-carousel-count]');
    const previous = carousel.querySelector('[data-carousel-prev]');
    const next = carousel.querySelector('[data-carousel-next]');
    const dots = carousel.closest('article')?.querySelectorAll('[data-carousel-dots] span') ?? [];

    const index = () => Math.round(track.scrollLeft / Math.max(1, track.clientWidth));
    const update = () => {
        const current = index();
        count.textContent = `${current + 1}/${slides}`;
        previous.hidden = current === 0;
        next.hidden = current >= slides - 1;
        dots.forEach((dot, dotIndex) => dot.classList.toggle('is-active', dotIndex === current));
    };
    const go = (step) => track.scrollTo({ left: (index() + step) * track.clientWidth, behavior: 'smooth' });

    track.addEventListener('scroll', () => window.requestAnimationFrame(update), { passive: true });
    previous.addEventListener('click', (event) => { event.stopPropagation(); go(-1); });
    next.addEventListener('click', (event) => { event.stopPropagation(); go(1); });
    previous.addEventListener('dblclick', (event) => event.stopPropagation());
    next.addEventListener('dblclick', (event) => event.stopPropagation());
});

// "Start a buddy read": 1) pick a book → 2) invite friends → 3) name it and start.
document.addEventListener('click', (event) => {
    if (!event.target.closest('[data-open-buddy-wizard]')) return;
    event.preventDefault();
    window.dispatchEvent(new CustomEvent('open-buddy-wizard'));
});

Alpine.data('buddyWizard', ({ books = [], friends = [], preselect = 0, old = null } = {}) => ({
    books,
    friends,
    isOpen: false,
    step: 1,
    bookId: null,
    invitees: [],
    title: '',
    bookSearch: '',
    friendSearch: '',

    init() {
        if (old) {
            this.bookId = old.book || null;
            this.invitees = old.invitees || [];
            this.title = old.title || '';
            this.step = this.bookId ? 2 : 1;
            this.isOpen = true;
        } else if (preselect && this.books.some((book) => book.id === preselect)) {
            this.bookId = preselect;
            this.step = 2;
            this.isOpen = true;
        }
        this.$watch('isOpen', (open) => document.documentElement.classList.toggle('overflow-hidden', open));
    },

    open() {
        this.isOpen = true;
        if (!this.bookId) this.step = 1;
    },

    matches(text, query) {
        return String(text || '').toLowerCase().includes(query.trim().toLowerCase());
    },

    filteredBooks() {
        return this.books.filter((book) => !this.bookSearch.trim() || this.matches(book.title, this.bookSearch) || this.matches(book.author, this.bookSearch));
    },

    filteredFriends() {
        return this.friends.filter((friend) => !this.friendSearch.trim() || this.matches(friend.name, this.friendSearch) || this.matches(friend.username, this.friendSearch));
    },

    toggle(id) {
        this.invitees = this.invitees.includes(id) ? this.invitees.filter((entry) => entry !== id) : [...this.invitees, id];
    },

    selectedBook() {
        return this.books.find((book) => book.id === this.bookId) || null;
    },

    selectedFriends() {
        return this.friends.filter((friend) => this.invitees.includes(friend.id));
    },
}));

// Book page: pick or drop PDFs, check them before sending, and show upload progress.
let pdfIds = 0;
Alpine.data('pdfUploader', ({ maxFiles = 5, maxMb = 20 } = {}) => ({
    maxFiles,
    files: [],
    errors: [],
    over: false,
    uploading: false,
    progress: 0,

    size(bytes) {
        return bytes >= 1048576 ? `${(bytes / 1048576).toFixed(1)} MB` : `${Math.max(1, Math.round(bytes / 1024))} KB`;
    },

    add(list) {
        this.errors = [];
        [...list].forEach((file) => {
            if (this.files.length >= this.maxFiles) {
                this.errors = [`You can upload up to ${this.maxFiles} PDFs at once.`];
                return;
            }
            const isPdf = file.type === 'application/pdf' || /\.pdf$/i.test(file.name);
            const error = !isPdf ? 'Not a PDF — this one will be skipped.'
                : file.size > maxMb * 1048576 ? `Larger than ${maxMb} MB — this one will be skipped.`
                : null;
            this.files.push({ id: ++pdfIds, file, error });
        });
    },

    remove(index) {
        this.files.splice(index, 1);
        this.errors = [];
    },

    validFiles() {
        return this.files.filter((entry) => !entry.error);
    },

    upload(form) {
        const valid = this.validFiles();
        if (!valid.length || this.uploading) return;
        const data = new FormData();
        data.append('_token', form.querySelector('input[name="_token"]').value);
        valid.forEach((entry) => data.append('pdfs[]', entry.file));

        this.uploading = true;
        this.progress = 0;
        this.errors = [];
        const request = new XMLHttpRequest();
        request.open('POST', form.action);
        request.setRequestHeader('Accept', 'application/json');
        request.upload.addEventListener('progress', (event) => {
            if (event.lengthComputable) this.progress = Math.round((event.loaded / event.total) * 100);
        });
        request.addEventListener('load', () => {
            let payload = {};
            try { payload = JSON.parse(request.responseText || '{}'); } catch { /* not JSON */ }
            if (request.status >= 200 && request.status < 300) {
                window.location.href = payload.redirect || window.location.href;
                window.location.reload();
                return;
            }
            this.uploading = false;
            this.errors = request.status === 413
                ? ['These files are too big for the server to accept at once. Try fewer files, or raise upload_max_filesize and post_max_size in php.ini.']
                : payload.errors ? Object.values(payload.errors).flat() : [payload.message || 'The upload didn’t work. Please try again.'];
        });
        request.addEventListener('error', () => {
            this.uploading = false;
            this.errors = ['The upload was interrupted. Check your connection and try again.'];
        });
        request.send(data);
    },
}));

// Live reader search on the Find readers page.
document.querySelectorAll('[data-reader-suggest]').forEach(attachReaderSuggest);

// Stay in place after actions. Replying, deleting, following and other buttons
// send a form and the page reloads; bring the reader back to where they were
// instead of the top. Forms marked data-scroll-top (e.g. a new post) skip this.
const SCROLL_KEY = 'bookworm-scroll';
document.addEventListener('submit', (event) => {
    const form = event.target;
    if (!(form instanceof HTMLFormElement) || form.hasAttribute('data-scroll-top')) return;
    if ((form.getAttribute('method') || 'get').toLowerCase() !== 'post') return;
    try {
        sessionStorage.setItem(SCROLL_KEY, JSON.stringify({ path: window.location.pathname + window.location.search, y: window.scrollY, at: Date.now() }));
    } catch {
        // Storage blocked: nothing to restore.
    }
});

(() => {
    let saved = null;
    try {
        saved = JSON.parse(sessionStorage.getItem(SCROLL_KEY) || 'null');
        sessionStorage.removeItem(SCROLL_KEY);
    } catch {
        return;
    }
    if (!saved || Date.now() - saved.at > 60_000 || saved.path !== window.location.pathname + window.location.search) return;
    const restore = () => window.scrollTo({ top: saved.y, left: 0, behavior: 'instant' });
    if ('scrollRestoration' in history) history.scrollRestoration = 'manual';
    restore();
    // Images loading in can shift the page; settle again once everything is in.
    window.addEventListener('load', restore, { once: true });
    window.setTimeout(restore, 250);
})();

Alpine.start();
