import * as pdfjsLib from 'pdfjs-dist';
import pdfWorkerUrl from 'pdfjs-dist/build/pdf.worker.min.mjs?url';
import 'pdfjs-dist/web/pdf_viewer.css';

pdfjsLib.GlobalWorkerOptions.workerSrc = pdfWorkerUrl;

function initializeReader(reader) {
    const pageViewport = reader.querySelector('[data-page-viewport]');
    const currentPageLabel = reader.querySelector('[data-current-page]');
    const pageCountLabel = reader.querySelector('[data-page-count]');
    const status = reader.querySelector('[data-reader-status]');
    const previousButton = reader.querySelector('[data-previous-page]');
    const nextButton = reader.querySelector('[data-next-page]');
    const zoomOutButton = reader.querySelector('[data-zoom-out]');
    const zoomInButton = reader.querySelector('[data-zoom-in]');
    const zoomLabel = reader.querySelector('[data-zoom-label]');
    const saveHighlightButton = reader.querySelector('[data-save-highlight]');
    const highlightsList = reader.querySelector('[data-highlights-list]');
    const highlightsEmpty = reader.querySelector('[data-highlights-empty]');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;

    let documentProxy;
    let pageNumber = 1;
    let scale = 1;
    let highlights = [];
    let pendingSelection = null;

    const setStatus = (message, isError = false) => {
        status.textContent = message;
        status.classList.toggle('text-red-700', isError);
        status.classList.toggle('dark:text-red-300', isError);
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
            const validationMessage = payload.errors
                ? Object.values(payload.errors).flat().join(' ')
                : null;
            throw new Error(validationMessage || payload.message || 'The request could not be completed.');
        }

        return payload;
    }

    function drawHighlights() {
        const overlay = pageViewport.querySelector('[data-highlight-overlay]');
        if (!overlay || !documentProxy) return;

        overlay.replaceChildren();
        highlights
            .filter((highlight) => Number(highlight.page_number) === pageNumber)
            .forEach((highlight) => {
                highlight.rectangles.forEach((rectangle) => {
                    const mark = document.createElement('span');
                    mark.className = 'pdf-reader-highlight';
                    mark.style.left = `${rectangle.x * 100}%`;
                    mark.style.top = `${rectangle.y * 100}%`;
                    mark.style.width = `${rectangle.width * 100}%`;
                    mark.style.height = `${rectangle.height * 100}%`;
                    mark.setAttribute('aria-hidden', 'true');
                    overlay.append(mark);
                });
            });
    }

    function renderHighlightList() {
        highlightsList.replaceChildren();
        highlightsEmpty.hidden = highlights.length > 0;

        highlights.forEach((highlight) => {
            const item = document.createElement('li');
            item.className = 'rounded-xl border border-[#eadfd4] p-3 dark:border-[#453538]';

            const quote = document.createElement('p');
            quote.className = 'break-words text-sm text-[#564541] dark:text-[#e3d3ca]';
            quote.textContent = `“${highlight.text}”`;

            const actions = document.createElement('div');
            actions.className = 'mt-2 flex items-center justify-between gap-2';

            const goToPage = document.createElement('button');
            goToPage.type = 'button';
            goToPage.className = 'min-h-11 text-sm font-semibold text-[#713640] underline focus:outline-none focus:ring-2 focus:ring-[#b98768] dark:text-[#e4b3a5]';
            goToPage.textContent = `Go to page ${highlight.page_number}`;
            goToPage.addEventListener('click', async () => {
                pageNumber = Number(highlight.page_number);
                await renderPage();
            });

            const deleteButton = document.createElement('button');
            deleteButton.type = 'button';
            deleteButton.className = 'min-h-11 px-2 text-sm font-semibold text-red-700 underline focus:outline-none focus:ring-2 focus:ring-red-500 dark:text-red-300';
            deleteButton.textContent = 'Remove';
            deleteButton.setAttribute('aria-label', `Remove highlight: ${highlight.text}`);
            deleteButton.addEventListener('click', async () => {
                deleteButton.disabled = true;
                try {
                    await requestJson(reader.dataset.deleteHighlightUrl.replace('__HIGHLIGHT__', highlight.id), {
                        method: 'DELETE',
                    });
                    highlights = highlights.filter((entry) => entry.id !== highlight.id);
                    renderHighlightList();
                    drawHighlights();
                    setStatus('Highlight removed.');
                } catch (error) {
                    deleteButton.disabled = false;
                    setStatus(error.message, true);
                }
            });

            actions.append(goToPage, deleteButton);
            item.append(quote, actions);
            highlightsList.append(item);
        });
    }

    async function renderPage() {
        const page = await documentProxy.getPage(pageNumber);
        const viewport = page.getViewport({ scale });
        const pixelRatio = Math.min(window.devicePixelRatio || 1, 2);
        const canvas = document.createElement('canvas');
        const context = canvas.getContext('2d');
        const overlay = document.createElement('div');
        const textLayerElement = document.createElement('div');

        if (!context) throw new Error('Your browser could not create a PDF page canvas.');

        pageViewport.replaceChildren();
        pageViewport.style.width = `${viewport.width}px`;
        pageViewport.style.height = `${viewport.height}px`;

        canvas.width = Math.floor(viewport.width * pixelRatio);
        canvas.height = Math.floor(viewport.height * pixelRatio);
        canvas.style.width = `${viewport.width}px`;
        canvas.style.height = `${viewport.height}px`;
        context.scale(pixelRatio, pixelRatio);

        overlay.dataset.highlightOverlay = '';
        overlay.className = 'pdf-reader-highlight-layer';
        textLayerElement.className = 'textLayer';
        textLayerElement.style.width = `${viewport.width}px`;
        textLayerElement.style.height = `${viewport.height}px`;

        pageViewport.append(canvas, overlay, textLayerElement);
        await page.render({ canvasContext: context, viewport }).promise;
        const textContent = await page.getTextContent();
        const textLayer = new pdfjsLib.TextLayer({
            textContentSource: textContent,
            container: textLayerElement,
            viewport,
        });
        await textLayer.render();

        currentPageLabel.textContent = String(pageNumber);
        previousButton.disabled = pageNumber <= 1;
        nextButton.disabled = pageNumber >= documentProxy.numPages;
        zoomOutButton.disabled = scale <= 0.7;
        zoomInButton.disabled = scale >= 2.5;
        zoomLabel.textContent = `${Math.round(scale * 100)}%`;
        drawHighlights();
    }

    function captureSelection() {
        const selection = window.getSelection();
        const selectedText = selection?.toString().trim();
        const range = selection?.rangeCount ? selection.getRangeAt(0) : null;
        const textLayer = pageViewport.querySelector('.textLayer');

        if (!selection || !selectedText || !range || !textLayer || !textLayer.contains(range.commonAncestorContainer)) {
            pendingSelection = null;
            saveHighlightButton.disabled = true;
            return;
        }

        const pageBounds = pageViewport.getBoundingClientRect();
        const rectangles = [...range.getClientRects()]
            .map((rectangle) => ({
                x: Math.max(0, rectangle.left - pageBounds.left) / pageBounds.width,
                y: Math.max(0, rectangle.top - pageBounds.top) / pageBounds.height,
                width: Math.min(rectangle.right, pageBounds.right) - Math.max(rectangle.left, pageBounds.left),
                height: Math.min(rectangle.bottom, pageBounds.bottom) - Math.max(rectangle.top, pageBounds.top),
            }))
            .filter((rectangle) => rectangle.width > 0 && rectangle.height > 0)
            .map((rectangle) => ({
                x: rectangle.x,
                y: rectangle.y,
                width: rectangle.width / pageBounds.width,
                height: rectangle.height / pageBounds.height,
            }));

        pendingSelection = rectangles.length
            ? { page_number: pageNumber, text: selectedText.slice(0, 2000), rectangles }
            : null;
        saveHighlightButton.disabled = !pendingSelection;
    }

    previousButton.addEventListener('click', async () => {
        if (pageNumber <= 1) return;
        pageNumber -= 1;
        await renderPage();
    });

    nextButton.addEventListener('click', async () => {
        if (pageNumber >= documentProxy.numPages) return;
        pageNumber += 1;
        await renderPage();
    });

    zoomOutButton.addEventListener('click', async () => {
        scale = Math.max(0.7, Math.round((scale - 0.1) * 10) / 10);
        await renderPage();
    });

    zoomInButton.addEventListener('click', async () => {
        scale = Math.min(2.5, Math.round((scale + 0.1) * 10) / 10);
        await renderPage();
    });

    document.addEventListener('selectionchange', captureSelection);
    saveHighlightButton.addEventListener('pointerdown', (event) => event.preventDefault());
    saveHighlightButton.addEventListener('click', async () => {
        captureSelection();
        if (!pendingSelection) {
            setStatus('Select some PDF text first, then save it as a highlight.', true);
            return;
        }

        saveHighlightButton.disabled = true;
        try {
            const highlight = await requestJson(reader.dataset.storeHighlightUrl, {
                method: 'POST',
                body: JSON.stringify(pendingSelection),
            });
            highlights.push(highlight);
            window.getSelection()?.removeAllRanges();
            pendingSelection = null;
            renderHighlightList();
            drawHighlights();
            setStatus('Highlight saved privately.');
        } catch (error) {
            setStatus(error.message, true);
            saveHighlightButton.disabled = false;
        }
    });

    async function loadReader() {
        const [loadedDocument, savedHighlights] = await Promise.all([
            pdfjsLib.getDocument({ url: reader.dataset.fileUrl, withCredentials: true }).promise,
            requestJson(reader.dataset.highlightsUrl),
        ]);

        documentProxy = loadedDocument;
        highlights = savedHighlights;
        pageCountLabel.textContent = String(documentProxy.numPages);
        const firstPage = await documentProxy.getPage(1);
        const baseViewport = firstPage.getViewport({ scale: 1 });
        const availableWidth = Math.max(pageViewport.parentElement.clientWidth - 40, 280);
        scale = Math.min(1.3, availableWidth / baseViewport.width);

        renderHighlightList();
        await renderPage();
        previousButton.disabled = true;
        setStatus('Select text on the page to highlight it. Your highlights save to your private library.');
    }

    loadReader().catch((error) => {
        console.error('Unable to load private PDF reader.', error);
        setStatus('This PDF could not be opened. Try downloading it or refresh the page.', true);
    });
}

document.querySelectorAll('[data-pdf-reader]').forEach(initializeReader);
