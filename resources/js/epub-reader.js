// EPUB reader (epub.js): paginated pages, themes, fonts, text size, contents,
// progress saved to the shelf (as a page of the book), and Barnaby's recap.
import ePub from 'epubjs';
import literataUrl from '@fontsource/literata/files/literata-latin-400-normal.woff2?url';
import garamondUrl from '@fontsource/eb-garamond/files/eb-garamond-latin-400-normal.woff2?url';
import atkinsonUrl from '@fontsource/atkinson-hyperlegible/files/atkinson-hyperlegible-latin-400-normal.woff2?url';
import { askBarnaby, renderBarnaby, typingIndicator } from './barnaby.js';

const THEMES = {
    paper: { body: { background: '#fffdf8', color: '#2a1a1d' } },
    sepia: { body: { background: '#f4ead5', color: '#4a3524' } },
    night: { body: { background: '#1d1416', color: '#e9ddd4' }, a: { color: '#f0a8b4' } },
};

const root = document.querySelector('[data-epub-reader]');
if (root) {
    const $ = (selector) => root.querySelector(selector);
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const store = (key, value) => { try { localStorage.setItem(key, JSON.stringify(value)); } catch { /* blocked */ } };
    const load = (key, fallback) => { try { return JSON.parse(localStorage.getItem(key)) ?? fallback; } catch { return fallback; } };
    const settings = { theme: 'paper', font: 'Literata', size: 100, ...load('bookworm-epub-settings', {}) };
    const totalPages = Number(root.dataset.totalPages) || 0;

    const book = ePub(root.dataset.fileUrl, { openAs: 'epub', requestCredentials: true });
    const rendition = book.renderTo($('[data-epub-view]'), { width: '100%', height: '100%', spread: 'auto', flow: 'paginated', allowScriptedContent: false });
    Object.entries(THEMES).forEach(([name, rules]) => rendition.themes.register(name, rules));

    // The book is drawn in its own frame: give it the reading fonts and comfortable spacing.
    const fontFaces = [['Literata', literataUrl], ['EB Garamond', garamondUrl], ['Atkinson Hyperlegible', atkinsonUrl]]
        .map(([name, url]) => `@font-face { font-family: "${name}"; src: url("${new URL(url, window.location.href).href}") format("woff2"); font-display: swap; }`)
        .join('\n');
    rendition.hooks.content.register((contents) => {
        const style = contents.document.createElement('style');
        style.textContent = `${fontFaces}\n p { line-height: 1.65 !important; } img { max-width: 100%; height: auto; }`;
        contents.document.head.append(style);
    });

    const apply = () => {
        rendition.themes.select(settings.theme);
        rendition.themes.fontSize(`${settings.size}%`);
        if (settings.font !== 'original') rendition.themes.override('font-family', `"${settings.font}", Georgia, serif`);
        else rendition.themes.override('font-family', '');
        root.dataset.theme = settings.theme;
        $('[data-epub-size-label]').textContent = `${settings.size}%`;
        root.querySelectorAll('[data-epub-theme]').forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.epubTheme === settings.theme)));
        root.querySelectorAll('[data-epub-font]').forEach((b) => b.setAttribute('aria-pressed', String(b.dataset.epubFont === settings.font)));
        store('bookworm-epub-settings', settings);
    };

    // Where you were (this device), then the contents list.
    const saved = load(root.dataset.bookKey, null);
    rendition.display(saved || undefined).then(apply);

    book.loaded.navigation.then((nav) => {
        const list = $('[data-epub-toc]');
        const add = (items, depth = 0) => items.forEach((item) => {
            const li = document.createElement('li');
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = item.label.trim();
            button.style.paddingLeft = `${0.75 + depth}rem`;
            button.addEventListener('click', () => { rendition.display(item.href); closePanels(); });
            li.append(button);
            list.append(li);
            if (item.subitems?.length) add(item.subitems, depth + 1);
        });
        add(nav.toc);
    });

    let percent = 0;
    let saveTimer;
    rendition.on('relocated', (location) => {
        store(root.dataset.bookKey, location.start.cfi);
        // Progress: which chapter you're in, and how far through it.
        const sections = book.spine.length || 1;
        const shown = location.start.displayed || { page: 1, total: 1 };
        const atEnd = location.atEnd ? 1 : 0;
        const value = atEnd || Math.min(1, (location.start.index + (shown.page - 1) / Math.max(1, shown.total)) / sections);
        percent = Math.round((value || 0) * 100);
        $('[data-epub-bar]').style.width = `${percent}%`;
        $('[data-epub-percent]').textContent = `${percent}%`;
        const chapter = book.navigation?.get(location.start.href);
        $('[data-epub-chapter]').textContent = chapter?.label?.trim() || ' ';
        // Move the bookmark on the shelf (pages of the book, when we know how many).
        window.clearTimeout(saveTimer);
        saveTimer = window.setTimeout(() => {
            if (!totalPages) return;
            const page = Math.max(1, Math.round(totalPages * (value || 0)));
            const body = new FormData();
            body.append('_token', csrf);
            body.append('status', value >= 0.995 ? 'read' : (root.dataset.readingStatus === 'read' ? 'read' : 'reading'));
            body.append('current_page', String(page));
            body.append('total_pages', String(totalPages));
            fetch(root.dataset.progressUrl, { method: 'POST', body, credentials: 'same-origin', headers: { Accept: 'application/json' } }).catch(() => {});
        }, 2500);
    });

    const next = () => rendition.next();
    const prev = () => rendition.prev();
    $('[data-epub-next]').addEventListener('click', next);
    $('[data-epub-prev]').addEventListener('click', prev);
    const keys = (event) => {
        if (event.key === 'ArrowRight' || event.key === 'PageDown') next();
        if (event.key === 'ArrowLeft' || event.key === 'PageUp') prev();
        if (event.key === 'Escape') closePanels();
    };
    document.addEventListener('keydown', keys);
    rendition.on('keyup', keys);
    // Swipe inside the book.
    let startX = null;
    rendition.on('touchstart', (event) => { startX = event.changedTouches[0].screenX; });
    rendition.on('touchend', (event) => {
        if (startX === null) return;
        const dx = event.changedTouches[0].screenX - startX;
        if (Math.abs(dx) > 50) (dx < 0 ? next : prev)();
        startX = null;
    });

    // Panels
    const panels = root.querySelectorAll('[data-epub-panel]');
    const closePanels = () => panels.forEach((p) => { p.hidden = true; });
    const openPanel = (name) => panels.forEach((p) => { p.hidden = p.dataset.epubPanel !== name ? true : !p.hidden; });
    root.querySelectorAll('[data-epub-toggle]').forEach((b) => b.addEventListener('click', () => openPanel(b.dataset.epubToggle)));
    root.querySelectorAll('[data-epub-theme]').forEach((b) => b.addEventListener('click', () => { settings.theme = b.dataset.epubTheme; apply(); }));
    root.querySelectorAll('[data-epub-font]').forEach((b) => b.addEventListener('click', () => { settings.font = b.dataset.epubFont; apply(); }));
    root.querySelectorAll('[data-epub-size]').forEach((b) => b.addEventListener('click', () => {
        settings.size = Math.min(180, Math.max(70, settings.size + Number(b.dataset.epubSize) * 10));
        apply();
    }));

    // Barnaby: the story so far, up to where you are
    $('[data-epub-recap]').addEventListener('click', async () => {
        openPanel('barnaby');
        const bubble = $('[data-epub-barnaby]');
        if (!totalPages) {
            bubble.textContent = 'Add the book’s page count on its page, and Barnaby can recap everything up to where you are.';
            return;
        }
        bubble.replaceChildren(typingIndicator('Barnaby is flipping back through the pages…'));
        const page = Math.max(1, Math.round(totalPages * percent / 100));
        const result = await askBarnaby(root.dataset.barnabyRecapUrl, { page });
        bubble.innerHTML = renderBarnaby(result.reply || '');
    });
}
