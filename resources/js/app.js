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

// Likes on posts and reviews (no page reload).
document.addEventListener('click', async (event) => {
    const button = event.target.closest('[data-like-url]');
    if (!button) return;
    event.preventDefault();
    const count = button.querySelector('[data-like-count]');
    const liked = button.getAttribute('aria-pressed') !== 'true';
    button.setAttribute('aria-pressed', String(liked));
    count.textContent = String(Number(count.textContent) + (liked ? 1 : -1));
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
        count.textContent = String(data.count);
    } catch {
        button.setAttribute('aria-pressed', String(!liked));
        count.textContent = String(Number(count.textContent) + (liked ? -1 : 1));
        window.bookwormToast?.('Couldn’t save that like. Please try again.', 'error');
    }
});

Alpine.start();
