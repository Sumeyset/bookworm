import * as pdfjsLib from 'pdfjs-dist';
import pdfWorkerUrl from 'pdfjs-dist/build/pdf.worker.min.mjs?url';
import 'pdfjs-dist/web/pdf_viewer.css';

// Reading fonts. @font-face files only download when a font is picked.
import '@fontsource/literata/400.css';
import '@fontsource/literata/400-italic.css';
import '@fontsource/literata/600.css';
import '@fontsource/eb-garamond/400.css';
import '@fontsource/eb-garamond/400-italic.css';
import '@fontsource/eb-garamond/600.css';
import '@fontsource/atkinson-hyperlegible/400.css';
import '@fontsource/atkinson-hyperlegible/400-italic.css';
import '@fontsource/atkinson-hyperlegible/700.css';

pdfjsLib.GlobalWorkerOptions.workerSrc = pdfWorkerUrl;

const SETTINGS_KEY = 'bookworm-reader-settings';
const FONTS = {
    literata: "Literata, Georgia, 'Times New Roman', serif",
    garamond: "'EB Garamond', Garamond, Georgia, serif",
    georgia: "Georgia, 'Times New Roman', serif",
    atkinson: "'Atkinson Hyperlegible', Verdana, sans-serif",
    sans: 'Figtree, system-ui, -apple-system, "Segoe UI", sans-serif',
};
const LEADING = { compact: 1.35, normal: 1.6, relaxed: 1.9 };
const MARGINS = { narrow: 20, normal: 36, wide: 56 };
const SIZE_MIN = 14;
const SIZE_MAX = 30;
const HIGHLIGHT_SUPPORTED = typeof CSS !== 'undefined' && 'highlights' in CSS && typeof Highlight !== 'undefined';

const defaultSettings = () => ({
    view: 'book',
    font: 'literata',
    size: window.innerWidth < 640 ? 17 : 19,
    leading: 'normal',
    margins: 'normal',
    align: 'justify',
    spread: 'auto',
    theme: document.documentElement.classList.contains('dark') ? 'night' : 'paper',
});

function loadSettings() {
    try {
        const saved = JSON.parse(localStorage.getItem(SETTINGS_KEY) || '{}');
        return { ...defaultSettings(), ...saved };
    } catch {
        return defaultSettings();
    }
}

function storeSettings(settings) {
    try {
        localStorage.setItem(SETTINGS_KEY, JSON.stringify(settings));
    } catch {
        // Private mode or blocked storage: settings just won't persist.
    }
}

const median = (values) => {
    if (!values.length) return 0;
    const sorted = [...values].sort((a, b) => a - b);
    return sorted[Math.floor(sorted.length / 2)];
};

const percentile = (values, p) => {
    if (!values.length) return 0;
    const sorted = [...values].sort((a, b) => a - b);
    return sorted[Math.min(sorted.length - 1, Math.floor(sorted.length * p))];
};

const cleanText = (text) => text.replace(/[​\s]+/g, ' ').trim();
const TERMINAL = /[.!?:;"”’»)\]…]$/;
const PAGE_NUMBER = /^\s*(\d{1,4}|[ivxlc]{1,7})\s*$/i;

/* ------------------------------------------------------------------ */
/* PDF text → lines → paragraphs                                       */
/* ------------------------------------------------------------------ */

async function extractPage(documentProxy, number) {
    const page = await documentProxy.getPage(number);
    const viewport = page.getViewport({ scale: 1 });
    const content = await page.getTextContent();
    const items = [];

    for (const item of content.items) {
        if (!('str' in item)) continue;
        const tx = pdfjsLib.Util.transform(viewport.transform, item.transform);
        const height = Math.hypot(tx[2], tx[3]) || Math.abs(item.height) || 10;
        items.push({
            str: item.str,
            eol: item.hasEOL,
            x: tx[4],
            top: tx[5] - height,
            h: height,
            w: Math.abs(item.width * viewport.scale),
        });
    }

    page.cleanup();
    return { number, width: viewport.width, height: viewport.height, items };
}

function buildLines(items) {
    const lines = [];
    let line = null;

    for (const item of items) {
        if (!item.str) {
            if (item.eol && line) line.closed = true;
            continue;
        }

        const sameLine = line && !line.closed
            && Math.abs(item.top - line.top) < Math.max(item.h, line.h) * 0.5
            && item.x >= line.x1 - line.h * 2;

        if (!sameLine) {
            line = { items: [], top: item.top, h: item.h, x0: item.x, x1: item.x + item.w, closed: false };
            lines.push(line);
        }

        line.items.push(item);
        line.x0 = Math.min(line.x0, item.x);
        line.x1 = Math.max(line.x1, item.x + item.w);
        line.h = Math.max(line.h, item.h);
        if (item.eol) line.closed = true;
    }

    return lines
        .filter((entry) => entry.items.some((item) => item.str.trim()))
        .map((entry) => ({ ...entry, text: entry.items.map((item) => item.str).join('') }));
}

function buildParagraphs(page) {
    let lines = buildLines(page.items);
    if (!lines.length) return [];

    // Drop a lone page number at the very top or bottom of the page.
    if (PAGE_NUMBER.test(lines[lines.length - 1].text)) lines = lines.slice(0, -1);
    if (lines.length && PAGE_NUMBER.test(lines[0].text)) lines = lines.slice(1);
    if (!lines.length) return [];

    const bodyHeight = median(lines.map((line) => line.h)) || 10;
    const left = percentile(lines.map((line) => line.x0), 0.2);
    const right = percentile(lines.map((line) => line.x1), 0.8);
    const paragraphs = [];
    let current = null;

    lines.forEach((line, index) => {
        const previous = lines[index - 1];
        const large = line.h > bodyHeight * 1.25;
        let startsNew = !current;

        if (previous && current) {
            const gap = line.top - (previous.top + previous.h);
            const previousLarge = previous.h > bodyHeight * 1.25;
            const indented = line.x0 > left + bodyHeight * 0.8 && line.x0 - previous.x0 > bodyHeight * 0.8;
            const previousShort = previous.x1 < right - bodyHeight * 2.5 && TERMINAL.test(previous.text.trim());
            startsNew = gap > bodyHeight * 0.9 || large !== previousLarge || indented || previousShort;
        }

        if (startsNew) {
            current = {
                lines: [],
                heading: large,
                indented: line.x0 > left + bodyHeight * 0.8,
            };
            paragraphs.push(current);
        }
        current.lines.push(line);
    });

    return paragraphs;
}

/* ------------------------------------------------------------------ */
/* Reader                                                              */
/* ------------------------------------------------------------------ */

function initializeReader(reader) {
    const $ = (selector) => reader.querySelector(selector);
    const $$ = (selector) => [...reader.querySelectorAll(selector)];

    const stage = $('[data-stage]');
    const bookView = $('[data-book-view]');
    const bookClip = $('[data-book-clip]');
    const flow = $('[data-flow]');
    const folioLeft = $('[data-folio-left]');
    const folioRight = $('[data-folio-right]');
    const pdfView = $('[data-pdf-view]');
    const pageViewport = $('[data-page-viewport]');
    const loading = $('[data-loading]');
    const loadingLabel = $('[data-loading-label]');
    const previousButton = $('[data-previous-page]');
    const nextButton = $('[data-next-page]');
    const scrubber = $('[data-scrubber]');
    const currentPageLabel = $('[data-current-page]');
    const pageCountLabel = $('[data-page-count]');
    const pageExtra = $('[data-page-extra]');
    const status = $('[data-reader-status]');
    const highlightsList = $('[data-highlights-list]');
    const highlightsEmpty = $('[data-highlights-empty]');
    const highlightCount = $('[data-highlight-count]');
    const toolbar = $('[data-selection-toolbar]');
    const zoomLabel = $('[data-zoom-label]');
    const sizeLabel = $('[data-size-label]');
    const sizeRange = $('[data-setting-range="size"]');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    const bookTitle = reader.dataset.bookTitle || '';
    const bookAuthor = reader.dataset.bookAuthor || '';

    let settings = loadSettings();
    let documentProxy;
    let highlights = [];

    // Original PDF view
    let pdfPage = 1;
    let zoom = 1;
    let pdfRenderToken = 0;

    // Book view
    let bookState = 'idle'; // idle | building | ready | empty
    let bookBuild = null;
    let items = []; // { page, x, y, w, h, text } – x/y/w/h normalised to the PDF page
    let spans = [];
    let markers = []; // markers[pdfPage] = element
    let pageItems = new Map(); // pdfPage → item indexes
    let layout = { cols: 1, colWidth: 0, gap: 0, step: 0, totalCols: 1, markerCols: [] };
    let spread = 0;
    let highlightRanges = new Map(); // highlight id → Range[]

    // Floating toolbar
    let toolbarTarget = null;

    // When leaving the book view, keep the reader on the same PDF page.
    let lastBookPdfPage = null;
    const rememberBookPosition = () => {
        if (isBook()) lastBookPdfPage = currentPdfPage();
    };

    /* ---------------- helpers ---------------- */

    const announce = (message, isError = false) => {
        status.textContent = message;
        if (isError || message) window.bookwormToast?.(message, isError ? 'error' : 'success');
    };

    async function requestJson(url, options = {}) {
        const response = await fetch(url, {
            credentials: 'same-origin',
            ...options,
            headers: {
                Accept: 'application/json',
                ...(options.body ? { 'Content-Type': 'application/json' } : {}),
                ...(csrfToken ? { 'X-CSRF-TOKEN': csrfToken } : {}),
                ...options.headers,
            },
        });

        const payload = await response.json().catch(() => ({}));
        if (!response.ok) {
            const validationMessage = payload.errors ? Object.values(payload.errors).flat().join(' ') : null;
            throw new Error(validationMessage || payload.message || 'The request could not be completed.');
        }

        return payload;
    }

    const debounce = (fn, wait) => {
        let timer;
        return (...args) => {
            window.clearTimeout(timer);
            timer = window.setTimeout(() => fn(...args), wait);
        };
    };

    const isBook = () => settings.view === 'book' && bookState === 'ready';

    /* ---------------- reading progress ---------------- */

    let progressTimer;
    let progressSaveInFlight = false;
    let lastSavedPage = null;

    function currentPdfPage() {
        if (!isBook()) return pdfPage;
        // The PDF page of the first word on the current book page.
        const index = anchorIndex();
        return index === null ? 1 : items[index].page;
    }

    function scheduleProgressSave() {
        if (!reader.dataset.progressUrl || !documentProxy) return;

        window.clearTimeout(progressTimer);
        progressTimer = window.setTimeout(async () => {
            const pageToSave = currentPdfPage();
            if (progressSaveInFlight || lastSavedPage === pageToSave) return;

            progressSaveInFlight = true;
            try {
                await requestJson(reader.dataset.progressUrl, {
                    method: 'POST',
                    body: JSON.stringify({
                        status: reader.dataset.readingStatus || 'reading',
                        current_page: pageToSave,
                        total_pages: documentProxy.numPages,
                    }),
                });
                lastSavedPage = pageToSave;
            } catch (error) {
                announce(`Reading progress could not be saved: ${error.message}`, true);
            } finally {
                progressSaveInFlight = false;
                if (currentPdfPage() !== pageToSave) scheduleProgressSave();
            }
        }, 800);
    }

    /* ---------------- settings ---------------- */

    function applySettings() {
        reader.dataset.theme = settings.theme;
        reader.dataset.align = settings.align;
        reader.dataset.view = settings.view;
        reader.style.setProperty('--reader-font', FONTS[settings.font] || FONTS.literata);
        reader.style.setProperty('--reader-size', `${settings.size}px`);
        reader.style.setProperty('--reader-leading', String(LEADING[settings.leading] || LEADING.normal));

        $$('[data-setting]').forEach((button) => {
            const checked = String(settings[button.dataset.setting]) === button.dataset.value;
            button.setAttribute('aria-checked', String(checked));
        });
        $$('[data-view-option]').forEach((button) => {
            button.setAttribute('aria-pressed', String(button.dataset.viewOption === settings.view));
        });
        $$('[data-book-only]').forEach((element) => { element.hidden = settings.view !== 'book'; });
        $$('[data-pdf-only]').forEach((element) => { element.hidden = settings.view !== 'pdf'; });
        sizeRange.value = String(settings.size);
        sizeLabel.textContent = `${settings.size}px`;
    }

    const relayoutSoon = debounce(() => relayout(), 80);

    async function updateSetting(key, value) {
        if (settings[key] === value) return;
        settings = { ...settings, [key]: value };
        storeSettings(settings);
        applySettings();

        if (key === 'view') {
            await showView();
        } else if (key === 'font' || key === 'size') {
            await document.fonts.load(`${settings.size}px ${FONTS[settings.font]}`).catch(() => {});
            relayoutSoon();
        } else if (key !== 'theme') {
            relayoutSoon();
        }
    }

    $$('[data-setting]').forEach((button) => {
        button.addEventListener('click', () => updateSetting(button.dataset.setting, button.dataset.value));
    });
    $$('[data-view-option]').forEach((button) => {
        button.addEventListener('click', () => updateSetting('view', button.dataset.viewOption));
    });
    sizeRange.addEventListener('input', () => updateSetting('size', Number(sizeRange.value)));
    $$('[data-step="size"]').forEach((button) => {
        button.addEventListener('click', () => {
            const next = Math.min(SIZE_MAX, Math.max(SIZE_MIN, settings.size + Number(button.dataset.delta)));
            updateSetting('size', next);
        });
    });
    $('[data-reset-settings]').addEventListener('click', async () => {
        const view = settings.view;
        settings = { ...defaultSettings(), view };
        storeSettings(settings);
        applySettings();
        await document.fonts.load(`${settings.size}px ${FONTS[settings.font]}`).catch(() => {});
        relayout();
    });

    /* ---------------- panels ---------------- */

    function closePanels() {
        $$('[data-panel]').forEach((panel) => { panel.hidden = true; });
        $$('[data-toggle-panel]').forEach((button) => button.setAttribute('aria-expanded', 'false'));
    }

    $$('[data-toggle-panel]').forEach((button) => {
        button.addEventListener('click', (event) => {
            event.stopPropagation();
            const panel = $(`[data-panel="${button.dataset.togglePanel}"]`);
            const willOpen = panel.hidden;
            closePanels();
            hideToolbar();
            panel.hidden = !willOpen;
            button.setAttribute('aria-expanded', String(willOpen));
            if (willOpen) panel.querySelector('button, input')?.focus({ preventScroll: true });
        });
    });
    $$('[data-close-panel]').forEach((button) => button.addEventListener('click', closePanels));

    $('[data-fullscreen]').addEventListener('click', () => {
        if (document.fullscreenElement) document.exitFullscreen?.();
        else reader.requestFullscreen?.().catch(() => announce('Full screen is not available here.', true));
    });
    document.addEventListener('fullscreenchange', () => {
        reader.classList.toggle('is-fullscreen', document.fullscreenElement === reader);
    });

    /* ---------------- book view: build ---------------- */

    async function buildBook() {
        if (bookBuild) return bookBuild;

        bookBuild = (async () => {
            bookState = 'building';
            const total = documentProxy.numPages;
            const pages = new Array(total);
            let done = 0;
            let next = 1;

            const worker = async () => {
                while (next <= total) {
                    const number = next;
                    next += 1;
                    pages[number - 1] = await extractPage(documentProxy, number);
                    done += 1;
                    if (settings.view === 'book') {
                        loadingLabel.textContent = `Setting the pages… ${Math.round((done / total) * 100)}%`;
                    }
                }
            };
            await Promise.all(Array.from({ length: Math.min(4, total) }, worker));

            const characterCount = pages.reduce((sum, page) => sum + page.items.reduce((count, item) => count + item.str.trim().length, 0), 0);
            if (characterCount < Math.max(40, total * 8)) {
                bookState = 'empty';
                return;
            }

            renderBookText(pages);
            bookState = 'ready';
        })();

        return bookBuild;
    }

    function renderBookText(pages) {
        const fragment = document.createDocumentFragment();
        items = [];
        spans = [];
        markers = [];
        pageItems = new Map();

        let block = null;
        let blockOpen = false;
        let lastSpan = null;
        let lastItem = null;

        const newBlock = (heading) => {
            block = document.createElement(heading ? 'h3' : 'p');
            block.className = `reader-block${heading ? ' reader-block--heading' : ''}`;
            fragment.append(block);
            lastSpan = null;
            lastItem = null;
        };

        const addSpace = () => block.append(document.createTextNode(' '));

        const addItem = (page, raw) => {
            const text = raw.str;
            const span = document.createElement('span');
            const index = items.length;
            span.dataset.i = String(index);
            span.textContent = text;
            block.append(span);
            items.push({
                page: page.number,
                x: raw.x / page.width,
                y: raw.top / page.height,
                w: raw.w / page.width,
                h: raw.h / page.height,
                text,
            });
            spans.push(span);
            if (!pageItems.has(page.number)) pageItems.set(page.number, []);
            pageItems.get(page.number).push(index);
            lastSpan = span;
            lastItem = raw;
        };

        // Join the end of one line to the start of the next, mending hyphenated words.
        const joinLines = (nextText) => {
            if (!lastSpan) return;
            const previous = lastSpan.textContent;
            if (/[A-Za-zÀ-ÿ]-$/.test(previous) && /^[a-zà-ÿ]/.test(nextText)) {
                lastSpan.textContent = previous.slice(0, -1);
                items[Number(lastSpan.dataset.i)].text = lastSpan.textContent;
            } else if (!/\s$/.test(previous) && !/^\s/.test(nextText)) {
                addSpace();
            }
        };

        pages.forEach((page) => {
            const paragraphs = buildParagraphs(page);
            const first = paragraphs[0];
            const continues = blockOpen && first && !first.heading && !first.indented;

            if (!continues && (!first || paragraphs.length === 0)) {
                // A page with no text still gets a marker so we can jump to it.
                if (!block) newBlock(false);
            }

            const marker = document.createElement('span');
            marker.className = 'reader-marker';
            marker.dataset.pdfPage = String(page.number);
            markers[page.number] = marker;

            paragraphs.forEach((paragraph, paragraphIndex) => {
                const continuing = paragraphIndex === 0 && continues;
                if (!continuing) newBlock(paragraph.heading);
                if (paragraphIndex === 0) block.append(marker);

                paragraph.lines.forEach((line, lineIndex) => {
                    if (lineIndex > 0 || continuing) joinLines(line.text);
                    line.items.forEach((item, itemIndex) => {
                        if (itemIndex > 0 && lastItem) {
                            const gap = item.x - (lastItem.x + lastItem.w);
                            if (gap > item.h * 0.12 && !/\s$/.test(lastSpan.textContent) && !/^\s/.test(item.str)) addSpace();
                        }
                        addItem(page, item);
                    });
                });

                blockOpen = !paragraph.heading && !TERMINAL.test(block.textContent.trim());
            });

            if (!paragraphs.length) block.append(marker);
        });

        const end = document.createElement('span');
        end.className = 'reader-marker';
        end.dataset.end = '';
        (block || fragment).append(end);

        flow.replaceChildren(fragment);
    }

    /* ---------------- book view: layout & paging ---------------- */

    function columnOf(element) {
        const rect = element.getClientRects()[0] || element.getBoundingClientRect();
        const flowRect = flow.getBoundingClientRect();
        return Math.max(0, Math.floor((rect.left - flowRect.left + 2) / (layout.colWidth + layout.gap)));
    }

    function anchorIndex() {
        if (!spans.length) return null;
        const firstCol = spread * layout.cols;
        let low = 0;
        let high = spans.length - 1;
        let found = spans.length - 1;
        while (low <= high) {
            const mid = (low + high) >> 1;
            if (columnOf(spans[mid]) >= firstCol) {
                found = mid;
                high = mid - 1;
            } else {
                low = mid + 1;
            }
        }
        return found;
    }

    function layoutBook() {
        const width = bookView.clientWidth;
        const height = bookView.clientHeight;
        if (!width || !height) return false;

        const cols = settings.spread === 'auto' && width >= 860 ? 2 : 1;
        const marginX = Math.round(Math.min(MARGINS[settings.margins] || MARGINS.normal, width * 0.07));
        const gap = marginX * 2;
        const maxPage = cols === 2 ? 600 : 680;
        const colWidth = Math.floor(Math.min(maxPage, (width - marginX * 2 - gap * (cols - 1)) / cols));
        const clipWidth = colWidth * cols + gap * (cols - 1);
        const padTop = Math.round(Math.min(44, height * 0.07));
        const padBottom = padTop + 12;
        const clipHeight = Math.max(160, height - padTop - padBottom);

        reader.style.setProperty('--book-width', `${clipWidth + marginX * 2}px`);
        reader.style.setProperty('--book-pad-x', `${marginX}px`);
        reader.style.setProperty('--book-pad-top', `${padTop}px`);
        reader.style.setProperty('--book-pad-bottom', `${padBottom}px`);
        reader.style.setProperty('--book-col', `${colWidth}px`);
        bookView.classList.toggle('is-spread', cols === 2);

        bookClip.style.width = `${clipWidth}px`;
        bookClip.style.height = `${clipHeight}px`;
        flow.style.width = `${clipWidth}px`;
        flow.style.height = `${clipHeight}px`;
        flow.style.columnCount = String(cols);
        flow.style.columnGap = `${gap}px`;

        layout = { ...layout, cols, colWidth, gap, step: clipWidth + gap };
        return true;
    }

    function measureBook() {
        const end = flow.querySelector('[data-end]');
        layout.totalCols = end ? columnOf(end) + 1 : 1;
        layout.markerCols = markers.map((marker) => (marker ? columnOf(marker) : undefined));
    }

    const totalSpreads = () => Math.max(1, Math.ceil(layout.totalCols / layout.cols));

    function goToSpread(target, animate = true) {
        spread = Math.max(0, Math.min(totalSpreads() - 1, target));
        flow.classList.toggle('is-animating', animate && !window.matchMedia('(prefers-reduced-motion: reduce)').matches);
        flow.style.transform = `translate3d(${-spread * layout.step}px, 0, 0)`;
        rememberBookPosition();
        updateChrome();
        scheduleProgressSave();
    }

    const goToColumn = (column, animate = false) => goToSpread(Math.floor(column / layout.cols), animate);

    async function relayout() {
        if (settings.view === 'pdf') {
            if (documentProxy) await renderPdfPage();
            return;
        }
        if (!isBook()) return;

        const anchor = layout.colWidth ? anchorIndex() : null;
        if (!layoutBook()) return;
        await document.fonts.ready;
        measureBook();
        if (anchor !== null && spans[anchor]) goToColumn(columnOf(spans[anchor]));
        else goToSpread(spread, false);
    }

    /* ---------------- original PDF view ---------------- */

    async function renderPdfPage() {
        const token = ++pdfRenderToken;
        const page = await documentProxy.getPage(pdfPage);
        const base = page.getViewport({ scale: 1 });
        const fit = Math.min((pdfView.clientWidth - 24) / base.width, (pdfView.clientHeight - 24) / base.height);
        const scale = Math.max(0.2, fit) * zoom;
        const viewport = page.getViewport({ scale });
        const pixelRatio = Math.min(window.devicePixelRatio || 1, 2);
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
        const overlay = document.createElement('div');
        const textLayerElement = document.createElement('div');

        if (!context) throw new Error('Your browser could not create a PDF page canvas.');

        canvas.width = Math.floor(viewport.width * pixelRatio);
        canvas.height = Math.floor(viewport.height * pixelRatio);
        canvas.style.width = `${viewport.width}px`;
        canvas.style.height = `${viewport.height}px`;
        context.scale(pixelRatio, pixelRatio);
        await page.render({ canvasContext: context, viewport }).promise;
        if (token !== pdfRenderToken) return;

        overlay.dataset.highlightOverlay = '';
        overlay.className = 'pdf-reader-highlight-layer';
        textLayerElement.className = 'textLayer';
        textLayerElement.style.width = `${viewport.width}px`;
        textLayerElement.style.height = `${viewport.height}px`;

        pageViewport.replaceChildren(canvas, overlay, textLayerElement);
        pageViewport.style.width = `${viewport.width}px`;
        pageViewport.style.height = `${viewport.height}px`;
        pageViewport.style.setProperty('--scale-factor', String(scale));

        const textLayer = new pdfjsLib.TextLayer({
            textContentSource: await page.getTextContent(),
            container: textLayerElement,
            viewport,
        });
        await textLayer.render();

        zoomLabel.textContent = zoom === 1 ? 'Fit' : `${Math.round(zoom * 100)}%`;
        drawPdfHighlights();
        updateChrome();
        scheduleProgressSave();
    }

    function drawPdfHighlights() {
        const overlay = pageViewport.querySelector('[data-highlight-overlay]');
        if (!overlay) return;

        overlay.replaceChildren();
        highlights
            .filter((highlight) => Number(highlight.page_number) === pdfPage)
            .forEach((highlight) => {
                highlight.rectangles.forEach((rectangle) => {
                    const mark = document.createElement('span');
                    mark.className = 'pdf-reader-highlight';
                    mark.dataset.highlightId = String(highlight.id);
                    mark.style.left = `${rectangle.x * 100}%`;
                    mark.style.top = `${rectangle.y * 100}%`;
                    mark.style.width = `${rectangle.width * 100}%`;
                    mark.style.height = `${rectangle.height * 100}%`;
                    mark.setAttribute('aria-hidden', 'true');
                    overlay.append(mark);
                });
            });
    }

    async function goToPdfPage(number) {
        pdfPage = Math.max(1, Math.min(documentProxy.numPages, number));
        await renderPdfPage();
    }

    $('[data-zoom-in]').addEventListener('click', () => { zoom = Math.min(3, Math.round((zoom + 0.15) * 100) / 100); renderPdfPage(); });
    $('[data-zoom-out]').addEventListener('click', () => { zoom = Math.max(0.5, Math.round((zoom - 0.15) * 100) / 100); renderPdfPage(); });
    $('[data-zoom-fit]').addEventListener('click', () => { zoom = 1; renderPdfPage(); });

    /* ---------------- shared chrome ---------------- */

    function updateChrome() {
        if (!documentProxy) return;

        if (isBook()) {
            const total = layout.totalCols;
            const first = spread * layout.cols + 1;
            const last = Math.min(total, first + layout.cols - 1);
            currentPageLabel.textContent = first === last ? String(first) : `${first}–${last}`;
            pageCountLabel.textContent = String(total);
            pageExtra.textContent = `· ${Math.round((last / total) * 100)}% · PDF page ${currentPdfPage()}`;
            folioLeft.textContent = String(first);
            folioRight.textContent = layout.cols === 2 && last > first ? String(last) : '';
            scrubber.max = String(totalSpreads());
            scrubber.value = String(spread + 1);
            previousButton.disabled = spread <= 0;
            nextButton.disabled = spread >= totalSpreads() - 1;
        } else {
            currentPageLabel.textContent = String(pdfPage);
            pageCountLabel.textContent = String(documentProxy.numPages);
            pageExtra.textContent = `· ${Math.round((pdfPage / documentProxy.numPages) * 100)}%`;
            scrubber.max = String(documentProxy.numPages);
            scrubber.value = String(pdfPage);
            previousButton.disabled = pdfPage <= 1;
            nextButton.disabled = pdfPage >= documentProxy.numPages;
        }
        scrubber.disabled = Number(scrubber.max) <= 1;
    }

    function turn(direction) {
        hideToolbar();
        if (isBook()) {
            goToSpread(spread + direction);
        } else if (documentProxy && settings.view === 'pdf') {
            const target = pdfPage + direction;
            if (target >= 1 && target <= documentProxy.numPages) goToPdfPage(target);
        }
    }

    previousButton.addEventListener('click', () => turn(-1));
    nextButton.addEventListener('click', () => turn(1));
    scrubber.addEventListener('input', () => {
        if (isBook()) goToSpread(Number(scrubber.value) - 1, false);
    });
    scrubber.addEventListener('change', () => {
        if (!isBook() && documentProxy) goToPdfPage(Number(scrubber.value));
    });

    async function showView() {
        hideToolbar();
        window.getSelection()?.removeAllRanges();

        if (settings.view === 'book') {
            const pageBefore = pdfPage;
            if (bookState !== 'ready') {
                bookView.hidden = true;
                pdfView.hidden = true;
                loading.hidden = false;
                loadingLabel.textContent = 'Setting the pages…';
                await buildBook();
            }

            if (bookState === 'empty') {
                loading.hidden = true;
                $('[data-view-option="book"]').disabled = true;
                $('[data-view-option="book"]').title = 'This PDF has no selectable text (it may be scanned), so only the original pages can be shown.';
                announce('This PDF looks scanned, so it opens in the original view.');
                settings = { ...settings, view: 'pdf' };
                applySettings();
                return showView();
            }

            loading.hidden = true;
            pdfView.hidden = true;
            bookView.hidden = false;
            reader.dataset.view = 'book';
            layoutBook();
            await document.fonts.ready;
            measureBook();
            drawBookHighlights();
            const column = layout.markerCols[pageBefore];
            goToColumn(column ?? 0);
            return;
        }

        const pageBefore = bookState === 'ready' && lastBookPdfPage !== null ? lastBookPdfPage : pdfPage;
        bookView.hidden = true;
        loading.hidden = true;
        pdfView.hidden = false;
        reader.dataset.view = 'pdf';
        pdfPage = pageBefore;
        await renderPdfPage();
    }

    /* ---------------- highlights: mapping ---------------- */

    function snapToWords(text, start, end) {
        const word = /[\p{L}\p{N}'’]/u;
        let s = start;
        let e = end;
        while (s > 0 && word.test(text[s - 1]) && word.test(text[s])) s -= 1;
        while (e < text.length && e > 0 && word.test(text[e - 1]) && word.test(text[e])) e += 1;
        return [s, e];
    }

    function rangesForHighlight(highlight) {
        const indexes = pageItems.get(Number(highlight.page_number)) || [];
        const ranges = [];

        indexes.forEach((index) => {
            const item = items[index];
            const node = spans[index].firstChild;
            if (!node || !item.text.length || item.w <= 0) return;
            let start = Infinity;
            let end = -Infinity;

            highlight.rectangles.forEach((rectangle) => {
                const vertical = Math.min(item.y + item.h, rectangle.y + rectangle.height) - Math.max(item.y, rectangle.y);
                if (vertical < Math.min(item.h, rectangle.height) * 0.4) return;
                const a = Math.max(item.x, rectangle.x);
                const b = Math.min(item.x + item.w, rectangle.x + rectangle.width);
                if (b - a <= item.w * 0.01) return;
                start = Math.min(start, Math.round(((a - item.x) / item.w) * item.text.length));
                end = Math.max(end, Math.round(((b - item.x) / item.w) * item.text.length));
            });

            if (end > start) {
                const [s, e] = snapToWords(item.text, Math.max(0, start), Math.min(item.text.length, end));
                const range = document.createRange();
                range.setStart(node, s);
                range.setEnd(node, Math.min(e, node.length));
                ranges.push(range);
            }
        });

        return ranges;
    }

    function drawBookHighlights(activeId = null) {
        if (bookState !== 'ready') return;
        highlightRanges = new Map(highlights.map((highlight) => [highlight.id, rangesForHighlight(highlight)]));
        if (!HIGHLIGHT_SUPPORTED) return;

        const all = [];
        const active = [];
        highlightRanges.forEach((ranges, id) => (id === activeId ? active : all).push(...ranges));
        CSS.highlights.set('bw-highlight', new Highlight(...all));
        CSS.highlights.set('bw-highlight-active', new Highlight(...active));
    }

    const redrawHighlights = (activeId = null) => {
        drawBookHighlights(activeId);
        drawPdfHighlights();
        renderHighlightList();
    };

    /* ---------------- selection → highlight payload ---------------- */

    function blockAt(node, offset, isEnd) {
        const element = node.nodeType === Node.TEXT_NODE ? node.parentElement : node;
        const block = element?.closest?.('.reader-block');
        if (block) return block;
        if (element === flow) {
            const children = flow.children;
            return children[Math.max(0, Math.min(children.length - 1, isEnd ? offset - 1 : offset))] || null;
        }
        return null;
    }

    function bookSelectionPayload(range) {
        const startBlock = blockAt(range.startContainer, range.startOffset, false);
        const endBlock = blockAt(range.endContainer, range.endOffset, true);
        if (!startBlock || !endBlock) return null;

        const firstIndex = Number(startBlock.querySelector('[data-i]')?.dataset.i ?? NaN);
        const lastSpan = [...endBlock.querySelectorAll('[data-i]')].pop();
        const lastIndex = Number(lastSpan?.dataset.i ?? NaN);
        if (Number.isNaN(firstIndex) || Number.isNaN(lastIndex)) return null;

        const parts = [];
        for (let index = firstIndex; index <= lastIndex; index += 1) {
            const span = spans[index];
            if (!range.intersectsNode(span)) continue;
            const node = span.firstChild;
            const length = node?.length ?? 0;
            const start = range.startContainer === node ? range.startOffset : 0;
            const end = range.endContainer === node ? range.endOffset : length;
            if (end > start) parts.push({ index, start, end, length });
        }
        if (!parts.length) return null;

        const page = items[parts[0].index].page;
        const rectangles = parts
            .filter((part) => items[part.index].page === page)
            .map((part) => {
                const item = items[part.index];
                return {
                    x: Math.min(1, Math.max(0, item.x + item.w * (part.start / part.length))),
                    y: Math.min(1, Math.max(0, item.y)),
                    width: Math.min(1, Math.max(0.0001, item.w * ((part.end - part.start) / part.length))),
                    height: Math.min(1, Math.max(0.0001, item.h)),
                };
            })
            .slice(0, 100);

        return { page_number: page, rectangles };
    }

    function pdfSelectionPayload(range) {
        const pageBounds = pageViewport.getBoundingClientRect();
        const rectangles = [...range.getClientRects()]
            .map((rectangle) => ({
                x: Math.max(0, rectangle.left - pageBounds.left) / pageBounds.width,
                y: Math.max(0, rectangle.top - pageBounds.top) / pageBounds.height,
                width: (Math.min(rectangle.right, pageBounds.right) - Math.max(rectangle.left, pageBounds.left)) / pageBounds.width,
                height: (Math.min(rectangle.bottom, pageBounds.bottom) - Math.max(rectangle.top, pageBounds.top)) / pageBounds.height,
            }))
            .filter((rectangle) => rectangle.width > 0 && rectangle.height > 0)
            .map((rectangle) => ({ ...rectangle, x: Math.min(1, rectangle.x), y: Math.min(1, rectangle.y) }))
            .slice(0, 100);

        return rectangles.length ? { page_number: pdfPage, rectangles } : null;
    }

    function currentSelection() {
        const selection = window.getSelection();
        if (!selection || selection.isCollapsed || !selection.rangeCount) return null;
        const range = selection.getRangeAt(0);
        const text = cleanText(selection.toString());
        if (!text) return null;

        if (!bookView.hidden && flow.contains(range.commonAncestorContainer)) {
            const payload = bookSelectionPayload(range);
            return payload ? { range, payload: { ...payload, text: text.slice(0, 2000) } } : null;
        }

        const textLayer = pageViewport.querySelector('.textLayer');
        if (!pdfView.hidden && textLayer?.contains(range.commonAncestorContainer)) {
            const payload = pdfSelectionPayload(range);
            return payload ? { range, payload: { ...payload, text: text.slice(0, 2000) } } : null;
        }

        return null;
    }

    /* ---------------- floating toolbar ---------------- */

    function placeToolbar(rect) {
        toolbar.hidden = false;
        const coarse = window.matchMedia('(pointer: coarse)').matches;
        const width = toolbar.offsetWidth;
        const height = toolbar.offsetHeight;
        const left = Math.max(8, Math.min(window.innerWidth - width - 8, rect.left + rect.width / 2 - width / 2));
        let top = coarse ? rect.bottom + 14 : rect.top - height - 10;
        if (top < 8) top = rect.bottom + 10;
        if (top + height > window.innerHeight - 8) top = Math.max(8, rect.top - height - 10);
        toolbar.style.left = `${left}px`;
        toolbar.style.top = `${top}px`;
    }

    function showToolbar(target, rect) {
        toolbarTarget = target;
        const isHighlight = target.kind === 'highlight';
        const quoted = isHighlight && target.highlight.quoted;
        toolbar.querySelector('[data-action="highlight"]').hidden = isHighlight;
        toolbar.querySelector('[data-action="remove"]').hidden = !isHighlight;
        const quoteButton = toolbar.querySelector('[data-action="quote"]');
        quoteButton.disabled = Boolean(quoted);
        toolbar.querySelector('[data-quote-label]').textContent = quoted ? 'Quoted' : 'Quote';
        toolbar.setAttribute('aria-label', isHighlight ? 'Saved highlight' : 'Selected text');
        placeToolbar(rect);
        if (isHighlight) {
            drawBookHighlights(target.highlight.id);
            pageViewport.querySelectorAll('.pdf-reader-highlight').forEach((mark) => {
                mark.classList.toggle('is-active', mark.dataset.highlightId === String(target.highlight.id));
            });
        }
    }

    function hideToolbar() {
        if (toolbarTarget?.kind === 'highlight') {
            drawBookHighlights();
            pageViewport.querySelectorAll('.pdf-reader-highlight.is-active').forEach((mark) => mark.classList.remove('is-active'));
        }
        toolbarTarget = null;
        toolbar.hidden = true;
    }

    const onSelectionChange = debounce(() => {
        const selected = currentSelection();
        if (selected) {
            const rects = [...selected.range.getClientRects()].filter((rect) => rect.width > 0);
            const box = rects.length ? rects[0] : selected.range.getBoundingClientRect();
            const union = rects.length
                ? rects.reduce((acc, rect) => (rect.top < acc.top + 2 ? { left: Math.min(acc.left, rect.left), right: Math.max(acc.right, rect.right), top: acc.top, bottom: acc.bottom } : acc), { left: box.left, right: box.right, top: box.top, bottom: rects[rects.length - 1].bottom })
                : box;
            showToolbar({ kind: 'selection', ...selected }, { left: union.left, width: union.right - union.left, top: union.top, bottom: union.bottom });
        } else if (toolbarTarget?.kind === 'selection') {
            hideToolbar();
        }
    }, 140);
    document.addEventListener('selectionchange', onSelectionChange);

    // Keep the text selected while pressing toolbar buttons.
    toolbar.addEventListener('pointerdown', (event) => event.preventDefault());
    toolbar.addEventListener('mousedown', (event) => event.preventDefault());

    async function saveHighlight(payload) {
        const highlight = await requestJson(reader.dataset.storeHighlightUrl, {
            method: 'POST',
            body: JSON.stringify(payload),
        });
        highlights.push({ quoted: false, ...highlight });
        highlights.sort((a, b) => a.page_number - b.page_number || a.id - b.id);
        return highlights.find((entry) => entry.id === highlight.id);
    }

    async function saveQuote(highlight) {
        await requestJson(reader.dataset.saveQuoteUrl.replace('__HIGHLIGHT__', highlight.id), { method: 'POST' });
        highlight.quoted = true;
    }

    async function removeHighlight(highlight) {
        await requestJson(reader.dataset.deleteHighlightUrl.replace('__HIGHLIGHT__', highlight.id), { method: 'DELETE' });
        highlights = highlights.filter((entry) => entry.id !== highlight.id);
    }

    const quoteWithCredit = (text, page) => {
        const credit = [bookTitle, bookAuthor].filter(Boolean).join(', ');
        return `“${text}”${credit ? ` — ${credit}` : ''}${page ? `, p. ${page}` : ''}`;
    };

    async function copyText(text) {
        try {
            await navigator.clipboard.writeText(text);
            announce('Copied with the book’s name.');
        } catch {
            window.prompt('Copy this quote', text);
        }
    }

    toolbar.addEventListener('click', async (event) => {
        const button = event.target.closest('[data-action]');
        if (!button || !toolbarTarget) return;
        const action = button.dataset.action;
        const target = toolbarTarget;

        // Sharing is not built yet; the button is a placeholder for now.
        if (action === 'share') return;

        if (action === 'copy') {
            const text = target.kind === 'selection' ? target.payload.text : target.highlight.text;
            const page = target.kind === 'selection' ? target.payload.page_number : target.highlight.page_number;
            await copyText(quoteWithCredit(text, page));
            return;
        }

        button.disabled = true;
        try {
            if (action === 'highlight') {
                await saveHighlight(target.payload);
                window.getSelection()?.removeAllRanges();
                hideToolbar();
                redrawHighlights();
                announce('Highlighted.');
            } else if (action === 'quote') {
                const highlight = target.kind === 'selection' ? await saveHighlight(target.payload) : target.highlight;
                await saveQuote(highlight);
                window.getSelection()?.removeAllRanges();
                hideToolbar();
                redrawHighlights();
                announce('Saved to your quotes.');
            } else if (action === 'remove') {
                await removeHighlight(target.highlight);
                hideToolbar();
                redrawHighlights();
                announce('Highlight removed.');
            }
        } catch (error) {
            announce(error.message, true);
        } finally {
            button.disabled = false;
        }
    });

    /* ---------------- clicking on highlights / page edges ---------------- */

    function caretAt(x, y) {
        if (document.caretPositionFromPoint) {
            const position = document.caretPositionFromPoint(x, y);
            return position ? { node: position.offsetNode, offset: position.offset } : null;
        }
        if (document.caretRangeFromPoint) {
            const range = document.caretRangeFromPoint(x, y);
            return range ? { node: range.startContainer, offset: range.startOffset } : null;
        }
        return null;
    }

    function highlightAtPoint(x, y) {
        if (!bookView.hidden) {
            const caret = caretAt(x, y);
            if (!caret || !flow.contains(caret.node)) return null;
            for (const highlight of highlights) {
                const ranges = highlightRanges.get(highlight.id) || [];
                const hit = ranges.find((range) => {
                    try {
                        const atEnd = caret.node === range.endContainer && caret.offset === range.endOffset;
                        return range.isPointInRange(caret.node, caret.offset) && !atEnd;
                    } catch {
                        return false;
                    }
                });
                if (hit) return { highlight, rect: hit.getBoundingClientRect() };
            }
            return null;
        }

        const bounds = pageViewport.getBoundingClientRect();
        const px = (x - bounds.left) / bounds.width;
        const py = (y - bounds.top) / bounds.height;
        for (const highlight of highlights) {
            if (Number(highlight.page_number) !== pdfPage) continue;
            const hit = highlight.rectangles.find((r) => px >= r.x && px <= r.x + r.width && py >= r.y && py <= r.y + r.height);
            if (hit) {
                return {
                    highlight,
                    rect: {
                        left: bounds.left + hit.x * bounds.width,
                        width: hit.width * bounds.width,
                        top: bounds.top + hit.y * bounds.height,
                        bottom: bounds.top + (hit.y + hit.height) * bounds.height,
                    },
                };
            }
        }
        return null;
    }

    stage.addEventListener('click', (event) => {
        if (event.target.closest('button, a, input')) return;
        closePanels();
        const selection = window.getSelection();
        if (selection && !selection.isCollapsed && cleanText(selection.toString())) return;

        const hit = highlightAtPoint(event.clientX, event.clientY);
        if (hit) {
            showToolbar({ kind: 'highlight', highlight: hit.highlight }, hit.rect);
            return;
        }
        if (toolbarTarget) {
            hideToolbar();
            return;
        }

        // Tap the outer edges of the page to turn it, like a book.
        const frame = (isBook() ? bookView : pdfView).getBoundingClientRect();
        const relative = (event.clientX - frame.left) / frame.width;
        if (relative < 0.18) turn(-1);
        else if (relative > 0.82) turn(1);
    });

    // Swipe to turn pages on touch screens.
    let swipe = null;
    stage.addEventListener('pointerdown', (event) => {
        if (event.pointerType !== 'mouse') swipe = { x: event.clientX, y: event.clientY, time: Date.now() };
    });
    stage.addEventListener('pointerup', (event) => {
        if (!swipe) return;
        const dx = event.clientX - swipe.x;
        const dy = event.clientY - swipe.y;
        const quick = Date.now() - swipe.time < 600;
        swipe = null;
        const selection = window.getSelection();
        if (selection && !selection.isCollapsed) return;
        if (quick && Math.abs(dx) > 50 && Math.abs(dy) < 60 && !(settings.view === 'pdf' && zoom > 1)) turn(dx < 0 ? 1 : -1);
    });
    stage.addEventListener('pointercancel', () => { swipe = null; });

    document.addEventListener('keydown', (event) => {
        const typing = /INPUT|TEXTAREA|SELECT/.test(document.activeElement?.tagName) || document.activeElement?.isContentEditable;
        if (event.key === 'Escape') {
            hideToolbar();
            closePanels();
            return;
        }
        if (typing || event.metaKey || event.ctrlKey || event.altKey) return;
        if (['ArrowRight', 'PageDown'].includes(event.key) || (event.key === ' ' && !event.shiftKey)) {
            event.preventDefault();
            turn(1);
        } else if (['ArrowLeft', 'PageUp'].includes(event.key) || (event.key === ' ' && event.shiftKey)) {
            event.preventDefault();
            turn(-1);
        }
    });

    window.addEventListener('scroll', () => { if (toolbarTarget?.kind === 'highlight') hideToolbar(); }, { passive: true });

    /* ---------------- highlights list ---------------- */

    async function goToHighlight(highlight) {
        closePanels();
        if (isBook()) {
            const ranges = highlightRanges.get(highlight.id) || [];
            const target = ranges[0]?.startContainer?.parentElement || markers[Number(highlight.page_number)];
            if (target) goToColumn(columnOf(target), true);
            drawBookHighlights(highlight.id);
            window.setTimeout(() => drawBookHighlights(), 1600);
        } else {
            await goToPdfPage(Number(highlight.page_number));
        }
    }

    function listButton(label, className = '') {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = `reader-chip ${className}`.trim();
        button.textContent = label;
        return button;
    }

    function renderHighlightList() {
        highlightsList.replaceChildren();
        highlightsEmpty.hidden = highlights.length > 0;
        highlightCount.hidden = highlights.length === 0;
        highlightCount.textContent = String(highlights.length);

        highlights.forEach((highlight) => {
            const item = document.createElement('li');
            item.className = 'reader-hl';

            const quote = document.createElement('button');
            quote.type = 'button';
            quote.className = 'reader-hl__text';
            quote.textContent = highlight.text;
            quote.setAttribute('aria-label', `Go to highlight on page ${highlight.page_number}: ${highlight.text}`);
            quote.addEventListener('click', () => goToHighlight(highlight));

            const meta = document.createElement('p');
            meta.className = 'reader-hl__meta';
            meta.textContent = `Page ${highlight.page_number}${highlight.quoted ? ' · In your quotes' : ''}`;

            const actions = document.createElement('div');
            actions.className = 'reader-hl__actions';

            const quoteButton = listButton(highlight.quoted ? 'Quoted' : 'Quote');
            quoteButton.disabled = Boolean(highlight.quoted);
            quoteButton.setAttribute('aria-label', `Save quote: ${highlight.text}`);
            quoteButton.addEventListener('click', async () => {
                quoteButton.disabled = true;
                try {
                    await saveQuote(highlight);
                    renderHighlightList();
                    announce('Saved to your quotes.');
                } catch (error) {
                    quoteButton.disabled = false;
                    announce(error.message, true);
                }
            });

            const copyButton = listButton('Copy');
            copyButton.addEventListener('click', () => copyText(quoteWithCredit(highlight.text, highlight.page_number)));

            // Share: placeholder only, wired up later.
            const shareButton = listButton('Share');
            shareButton.dataset.shareHighlight = String(highlight.id);
            shareButton.title = 'Sharing is coming soon';

            const deleteButton = listButton('Remove', 'reader-chip--danger');
            deleteButton.setAttribute('aria-label', `Remove highlight: ${highlight.text}`);
            deleteButton.addEventListener('click', async () => {
                deleteButton.disabled = true;
                try {
                    await removeHighlight(highlight);
                    redrawHighlights();
                    announce('Highlight removed.');
                } catch (error) {
                    deleteButton.disabled = false;
                    announce(error.message, true);
                }
            });

            actions.append(quoteButton, copyButton, shareButton, deleteButton);
            item.append(quote, meta, actions);
            highlightsList.append(item);
        });
    }

    /* ---------------- boot ---------------- */

    new ResizeObserver(debounce(() => {
        hideToolbar();
        relayout();
    }, 120)).observe(stage);

    async function loadReader() {
        applySettings();
        const [loadedDocument, savedHighlights] = await Promise.all([
            pdfjsLib.getDocument({ url: reader.dataset.fileUrl, withCredentials: true }).promise,
            requestJson(reader.dataset.highlightsUrl),
        ]);

        documentProxy = loadedDocument;
        highlights = savedHighlights.map((highlight) => ({ ...highlight, quoted: Boolean(highlight.quoted) }));
        pdfPage = Math.min(documentProxy.numPages, Math.max(1, Number(reader.dataset.initialPage) || 1));
        lastSavedPage = pdfPage;
        lastBookPdfPage = pdfPage;
        renderHighlightList();

        await document.fonts.load(`${settings.size}px ${FONTS[settings.font]}`).catch(() => {});
        await showView();
        announce('');
    }

    loadReader().catch((error) => {
        console.error('Unable to load private PDF reader.', error);
        loading.hidden = false;
        loading.querySelector('.reader-spinner')?.remove();
        loadingLabel.textContent = 'This PDF could not be opened. Try downloading it or refresh the page.';
        announce('This PDF could not be opened.', true);
    });
}

document.querySelectorAll('[data-pdf-reader]').forEach(initializeReader);
