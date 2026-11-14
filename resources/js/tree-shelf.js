/**
 * Living book tree: drag books between branches and open a book like a real
 * book to see / edit its details. Loaded only on profile pages.
 */

const csrfToken = () => document.querySelector('meta[name="csrf-token"]')?.content ?? '';

async function api(url, method, body) {
    const response = await fetch(url, {
        method,
        credentials: 'same-origin',
        headers: {
            Accept: 'application/json',
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken(),
            'X-Requested-With': 'XMLHttpRequest',
        },
        body: body ? JSON.stringify(body) : undefined,
    });
    const data = await response.json().catch(() => ({}));
    if (!response.ok) {
        const firstError = data.errors ? Object.values(data.errors)[0]?.[0] : null;
        throw new Error(firstError || data.error || data.message || 'Something went wrong. Please try again.');
    }
    return data;
}

const toast = (message, tone = 'success') => window.bookwormToast?.(message, tone);
const readBook = (el) => JSON.parse(el.dataset.book || '{}');

/** Refresh the little ribbon / progress bar on a book after it changes. */
function decorateBook(el, data) {
    el.dataset.book = JSON.stringify(data);
    el.classList.remove('tree-book--want_to_read', 'tree-book--reading', 'tree-book--read');
    el.classList.add(`tree-book--${data.status}`);
    const progress = el.querySelector('.tree-book__progress');
    if (progress) {
        progress.hidden = !(data.status === 'reading' && data.progress !== null && data.progress !== undefined);
        progress.firstElementChild.style.width = `${data.progress ?? 0}%`;
    }
    const status = data.status_label === 'Currently reading' ? 'Reading' : data.status_label;
    el.setAttribute('aria-label', `${data.title}${data.author ? ` by ${data.author}` : ''}, ${status}`);
}

function initTree(tree) {
    const editable = tree.dataset.editable === 'true';
    const capacity = Number(tree.dataset.capacity) || 3;
    const shelves = JSON.parse(tree.dataset.shelves || '[]');
    const emptyTemplate = tree.querySelector('[data-empty-slot-template]');
    const popup = document.getElementById('book-popup');
    let suppressClick = false;

    const shelfEl = (slot) => tree.querySelector(`.tree-shelf[data-slot="${slot}"]`);
    const shelfTitle = (slot) => shelves.find((s) => Number(s.slot) === Number(slot))?.title ?? `Branch ${Number(slot) + 1}`;
    const booksOn = (shelf) => [...shelf.querySelectorAll('.tree-book')];

    function refreshEmpties(shelf) {
        if (!editable || !shelf || !emptyTemplate) return;
        const container = shelf.querySelector('.tree-shelf__books');
        container.querySelectorAll('.tree-slot-empty').forEach((el) => el.remove());
        const missing = Math.max(0, capacity - booksOn(shelf).length);
        for (let i = 0; i < missing; i += 1) {
            container.appendChild(emptyTemplate.content.firstElementChild.cloneNode(true));
        }
    }

    function applyOrders(orders) {
        let grew = false;
        Object.entries(orders).forEach(([slot, ids]) => {
            const shelf = shelfEl(slot);
            if (!shelf) return;
            const container = shelf.querySelector('.tree-shelf__books');
            ids.forEach((id) => {
                const book = tree.querySelector(`.tree-book[data-book-id="${id}"]`);
                if (!book) return;
                const data = readBook(book);
                data.slot = Number(slot);
                book.dataset.book = JSON.stringify(data);
                container.insertBefore(book, container.querySelector('.tree-slot-empty'));
            });
            refreshEmpties(shelf);
            if (ids.length) shelf.classList.remove('tree-shelf--quiet');
            if (ids.length && shelf.classList.contains('tree-shelf--sprouting')) grew = true;
        });
        if (grew) {
            toast('Your tree grew taller! 🌱');
            window.setTimeout(() => window.location.reload(), 900);
        }
    }

    async function moveBook(book, slot, position = null) {
        const data = readBook(book);
        const result = await api(data.move_url, 'PATCH', { slot: Number(slot), position });
        applyOrders(result.shelves);
        return result;
    }

    /* ---------------- Drag & drop (pointer events: mouse + touch) ---------------- */

    if (editable) {
        let drag = null;
        let scrollTimer = null;

        const clearTargets = () => {
            tree.querySelectorAll('.is-drop-target').forEach((el) => el.classList.remove('is-drop-target', 'is-full'));
            tree.querySelectorAll('.is-swap-target, .is-insert-before').forEach((el) => el.classList.remove('is-swap-target', 'is-insert-before'));
        };

        const cleanup = () => {
            if (!drag) return;
            window.clearTimeout(drag.timer);
            window.clearInterval(scrollTimer);
            drag.ghost?.remove();
            drag.book.classList.remove('is-dragging', 'is-lifting');
            tree.classList.remove('is-dragging');
            clearTargets();
            drag = null;
        };

        const start = () => {
            if (!drag) return;
            const rect = drag.book.getBoundingClientRect();
            drag.started = true;
            drag.offsetX = drag.x - rect.left;
            drag.offsetY = drag.y - rect.top;
            drag.ghost = drag.book.cloneNode(true);
            drag.ghost.classList.add('tree-book-ghost');
            drag.ghost.removeAttribute('data-book-id');
            drag.ghost.style.width = `${rect.width}px`;
            drag.ghost.style.height = `${rect.height}px`;
            document.body.appendChild(drag.ghost);
            drag.book.classList.add('is-dragging');
            tree.classList.add('is-dragging');
            navigator.vibrate?.(12);
            moveGhost(drag.x, drag.y);
            scrollTimer = window.setInterval(() => {
                if (!drag) return;
                const edge = 70;
                if (drag.lastY < edge) window.scrollBy(0, -14);
                else if (drag.lastY > window.innerHeight - edge) window.scrollBy(0, 14);
            }, 16);
        };

        const moveGhost = (x, y) => {
            drag.lastY = y;
            drag.ghost.style.transform = `translate(${x - drag.offsetX}px, ${y - drag.offsetY}px) rotate(-4deg) scale(1.06)`;
        };

        const findTarget = (x, y) => {
            clearTargets();
            const shelf = document.elementFromPoint(x, y)?.closest('.tree-shelf[data-slot]');
            if (!shelf || !tree.contains(shelf)) return null;
            const sameShelf = shelf === drag.book.closest('.tree-shelf');
            const others = booksOn(shelf).filter((b) => b !== drag.book);
            const position = others.filter((b) => {
                const r = b.getBoundingClientRect();
                return x > r.left + r.width / 2;
            }).length;
            const full = !sameShelf && others.length >= capacity;
            shelf.classList.add('is-drop-target');
            if (full) {
                shelf.classList.add('is-full');
                others[Math.min(position, others.length - 1)]?.classList.add('is-swap-target');
            } else {
                others[position]?.classList.add('is-insert-before');
            }
            return { shelf, position, full, others };
        };

        tree.addEventListener('pointerdown', (event) => {
            const book = event.target.closest('.tree-book');
            if (!book || event.button > 0 || drag) return;
            drag = { book, x: event.clientX, y: event.clientY, id: event.pointerId, started: false, touch: event.pointerType !== 'mouse' };
            if (drag.touch) {
                book.classList.add('is-lifting');
                drag.timer = window.setTimeout(start, 350);
            }
        });

        window.addEventListener('pointermove', (event) => {
            if (!drag || event.pointerId !== drag.id) return;
            const distance = Math.hypot(event.clientX - drag.x, event.clientY - drag.y);
            if (!drag.started) {
                if (drag.touch) {
                    if (distance > 10) cleanup(); // the reader is scrolling, not dragging
                    return;
                }
                if (distance < 6) return;
                start();
            }
            moveGhost(event.clientX, event.clientY);
            drag.target = findTarget(event.clientX, event.clientY);
        });

        window.addEventListener('pointerup', async (event) => {
            if (!drag || event.pointerId !== drag.id) return;
            if (!drag.started) {
                cleanup();
                return;
            }
            suppressClick = true;
            window.setTimeout(() => { suppressClick = false; }, 50);

            const { book, target } = drag;
            const fromShelf = book.closest('.tree-shelf');
            cleanup();
            if (!target) return;

            const toShelf = target.shelf;
            const slot = toShelf.dataset.slot;
            const container = toShelf.querySelector('.tree-shelf__books');

            // Move it on screen straight away, then confirm with the server.
            if (target.full) {
                const index = Math.min(target.position, target.others.length - 1);
                const swapped = target.others[index];
                fromShelf.querySelector('.tree-shelf__books').insertBefore(swapped, book);
                container.insertBefore(book, target.others[index + 1] ?? container.querySelector('.tree-slot-empty'));
            } else {
                container.insertBefore(book, target.others[target.position] ?? container.querySelector('.tree-slot-empty'));
            }
            refreshEmpties(fromShelf);
            refreshEmpties(toShelf);
            book.classList.add('just-landed');
            window.setTimeout(() => book.classList.remove('just-landed'), 500);

            try {
                await moveBook(book, slot, target.position);
                if (fromShelf !== toShelf) {
                    toast(target.full ? `Swapped onto “${shelfTitle(slot)}”` : `Moved to “${shelfTitle(slot)}”`);
                }
            } catch (error) {
                toast(error.message, 'error');
                window.setTimeout(() => window.location.reload(), 1200);
            }
        });

        window.addEventListener('pointercancel', cleanup);
        window.addEventListener('keydown', (event) => { if (event.key === 'Escape') cleanup(); });
        document.addEventListener('touchmove', (event) => { if (drag?.started) event.preventDefault(); }, { passive: false });
        tree.addEventListener('contextmenu', (event) => { if (event.target.closest('.tree-book')) event.preventDefault(); });
        tree.addEventListener('dragstart', (event) => event.preventDefault());
    }

    /* ---------------- Rename a branch ---------------- */

    const renameDialog = document.getElementById('rename-shelf-dialog');
    tree.querySelectorAll('[data-rename-slot]').forEach((button) => {
        button.addEventListener('click', () => {
            const form = renameDialog.querySelector('[data-rename-form]');
            form.action = `/shelves/${button.dataset.renameSlot}/title`;
            const input = form.querySelector('input[name="title"]');
            input.value = button.dataset.title;
            renameDialog.showModal();
            input.select();
        });
    });
    renameDialog?.querySelector('[data-close-dialog]')?.addEventListener('click', () => renameDialog.close());

    /* ---------------- Open-book popup ---------------- */

    if (!popup) return;
    const form = popup.querySelector('[data-popup-form]');
    const field = (name) => popup.querySelector(`[data-pop="${name}"]`);
    const setText = (name, value) => { const el = field(name); if (el) el.textContent = value ?? ''; };
    let current = null; // { el, data, rating }

    const stars = popup.querySelector('[data-pop-stars]');
    const paintStars = (rating) => {
        stars.querySelectorAll('.popup-star').forEach((star) => {
            const n = Number(star.dataset.star);
            const fill = rating >= n ? 100 : rating >= n - 0.5 ? 50 : 0;
            star.querySelector('.popup-star__fill').style.clipPath = `inset(0 ${100 - fill}% 0 0)`;
        });
        setText('rating-text', rating ? `${rating} / 5` : (editable ? 'Tap to rate' : 'Not rated'));
    };

    const inputs = {
        note: form.querySelector('[name="note"]'),
        current: form.querySelector('[name="current_page"]'),
        total: form.querySelector('[name="total_pages"]'),
        slider: form.querySelector('[data-page-slider]'),
        move: form.querySelector('[data-pop-move]'),
    };
    const statusValue = () => form.querySelector('[name="status"]:checked')?.value;

    const paintBookmark = () => {
        if (!inputs.current) return;
        const page = Number(inputs.current.value) || 0;
        const total = Number(inputs.total.value) || 0;
        const status = statusValue();
        const percent = status === 'read' ? 100 : total ? Math.min(100, Math.round((page / total) * 100)) : 0;
        inputs.slider.max = String(total || 100);
        inputs.slider.value = String(status === 'read' ? (total || 100) : page);
        inputs.slider.disabled = !total;
        inputs.slider.style.setProperty('--fill', `${percent}%`);
        popup.style.setProperty('--bookmark', `${percent}%`);
        setText('percent', total ? `${percent}%` : '');
        let text;
        if (status === 'read') text = 'Finished — the end! 🎉';
        else if (status === 'want_to_read' && !page) text = 'Not started yet';
        else if (page && total) text = `Page ${page} of ${total}`;
        else if (page) text = `Page ${page}`;
        else text = 'Tucked in at the start';
        setText('bookmark-text', text);
        const error = popup.querySelector('[data-pop-error]');
        const invalid = total && page > total;
        error.hidden = !invalid;
        error.textContent = invalid ? 'Your bookmark can’t be past the last page.' : '';
    };

    const fill = (data) => {
        current.data = data;
        current.rating = data.rating ?? 0;
        setText('title', data.title);
        setText('byline', [data.author || 'Unknown author', data.year].filter(Boolean).join(' · '));
        setText('shelf', `On the “${shelfTitle(data.slot)}” branch`);
        setText('status_label', data.status_label);
        setText('review', data.review ? `“${data.review}”` : '');
        setText('read_at', data.read_at || '—');
        setText('added_at', data.added_at || '—');
        setText('started_at', data.started_at || '—');
        setText('bookmark_moved', data.bookmark_moved || '—');

        const cover = popup.querySelector('[data-pop-cover]');
        const fallback = popup.querySelector('[data-pop-cover-fallback]');
        fallback.textContent = data.title;
        cover.hidden = !data.cover;
        if (data.cover) {
            cover.src = data.cover;
            cover.alt = `Cover of ${data.title}`;
            cover.onerror = () => { cover.hidden = true; };
        }

        popup.querySelectorAll('[data-pop-link]').forEach((link) => {
            const url = data[link.dataset.popLink];
            link.hidden = !url;
            if (url) link.href = url;
        });

        paintStars(current.rating);
        popup.dataset.status = data.status;

        if (editable) {
            form.querySelectorAll('[name="status"]').forEach((radio) => { radio.checked = radio.value === data.status; });
            inputs.note.value = data.note ?? '';
            inputs.current.value = data.current_page ?? '';
            inputs.total.value = data.total_pages ?? '';
            inputs.move.value = String(data.slot);
            popup.querySelector('[data-pop-saved]').textContent = '';
            paintBookmark();
        }
    };

    const open = (el) => {
        current = { el };
        fill(readBook(el));
        popup.showModal();
        popup.classList.remove('is-closing');
        popup.querySelector('[data-popup-close]').focus();
    };

    tree.addEventListener('click', (event) => {
        const book = event.target.closest('.tree-book');
        if (!book || suppressClick) return;
        open(book);
    });

    const close = () => {
        popup.classList.add('is-closing');
        window.setTimeout(() => { popup.close(); popup.classList.remove('is-closing'); current?.el?.focus(); }, 180);
    };
    popup.querySelector('[data-popup-close]').addEventListener('click', (event) => { event.preventDefault(); close(); });
    popup.addEventListener('click', (event) => { if (event.target === popup) close(); });
    popup.addEventListener('cancel', (event) => { event.preventDefault(); close(); });

    if (!editable) return;

    stars.addEventListener('click', (event) => {
        const button = event.target.closest('[data-rate]');
        if (!button) return;
        current.rating = Number(button.dataset.rate);
        paintStars(current.rating);
    });
    stars.addEventListener('mouseover', (event) => {
        const button = event.target.closest('[data-rate]');
        if (button) paintStars(Number(button.dataset.rate));
    });
    stars.addEventListener('mouseleave', () => paintStars(current?.rating ?? 0));
    popup.querySelector('[data-clear-rating]').addEventListener('click', () => {
        current.rating = 0;
        paintStars(0);
    });

    form.querySelectorAll('[name="status"]').forEach((radio) => radio.addEventListener('change', () => {
        popup.dataset.status = statusValue();
        if (statusValue() === 'read' && inputs.total.value) inputs.current.value = inputs.total.value;
        paintBookmark();
    }));
    [inputs.current, inputs.total].forEach((input) => input.addEventListener('input', paintBookmark));
    inputs.slider.addEventListener('input', () => {
        inputs.current.value = inputs.slider.value;
        const reading = form.querySelector('[name="status"][value="reading"]');
        if (statusValue() !== 'reading' && Number(inputs.slider.value) < Number(inputs.total.value)) reading.checked = true;
        popup.dataset.status = statusValue();
        paintBookmark();
    });

    const save = async () => {
        const button = popup.querySelector('[data-pop-save]');
        const saved = popup.querySelector('[data-pop-saved]');
        const toNumber = (value) => (value === '' || value === null ? null : Number(value));
        button.disabled = true;
        saved.textContent = 'Saving…';
        try {
            const result = await api(current.data.save_url, 'PATCH', {
                status: statusValue(),
                current_page: toNumber(inputs.current.value),
                total_pages: toNumber(inputs.total.value),
                note: inputs.note.value.trim() || null,
                rating: current.rating || null,
            });
            decorateBook(current.el, result.book);
            fill(result.book);
            saved.textContent = 'Saved ✓';
            toast('Saved to your shelf');
        } catch (error) {
            saved.textContent = '';
            toast(error.message, 'error');
        } finally {
            button.disabled = false;
        }
    };

    popup.querySelector('[data-pop-save]').addEventListener('click', save);
    form.addEventListener('submit', (event) => {
        if (event.submitter?.hasAttribute('data-popup-close')) return;
        event.preventDefault();
        save();
    });
    form.addEventListener('keydown', (event) => {
        if (event.key === 'Enter' && (event.metaKey || event.ctrlKey)) {
            event.preventDefault();
            save();
        }
    });

    inputs.move.addEventListener('change', async () => {
        const slot = inputs.move.value;
        try {
            await moveBook(current.el, slot, null);
            current.data = readBook(current.el);
            setText('shelf', `On the “${shelfTitle(slot)}” branch`);
            toast(`Moved to “${shelfTitle(slot)}”`);
        } catch (error) {
            inputs.move.value = String(current.data.slot);
            toast(error.message, 'error');
        }
    });

    popup.querySelector('[data-pop-remove]').addEventListener('click', async () => {
        if (!window.confirm(`Take “${current.data.title}” off your tree? Your rating stays on the book page.`)) return;
        try {
            await api(current.data.remove_url, 'DELETE');
            const shelf = current.el.closest('.tree-shelf');
            current.el.remove();
            refreshEmpties(shelf);
            close();
            toast('Book removed from your tree');
        } catch (error) {
            toast(error.message, 'error');
        }
    });
}

document.querySelectorAll('[data-book-tree]').forEach(initTree);

/* ---------------- Save the tree as an image ---------------- */

const loadImage = (src) => new Promise((resolve) => {
    const img = new Image();
    img.onload = () => resolve(img);
    img.onerror = () => resolve(null);
    img.src = src;
});

async function renderTreeImage(button) {
    const frame = document.querySelector('.book-tree__frame');
    if (!frame) return;
    const box = frame.getBoundingClientRect();
    const scale = Math.min(2, 1600 / box.width);
    const header = 120 * scale;
    const canvas = document.createElement('canvas');
    canvas.width = Math.round(box.width * scale);
    canvas.height = Math.round(box.height * scale + header);
    const ctx = canvas.getContext('2d');

    // Title band
    const gradient = ctx.createLinearGradient(0, 0, canvas.width, 0);
    gradient.addColorStop(0, '#4A1220');
    gradient.addColorStop(1, '#8B2A3D');
    ctx.fillStyle = gradient;
    ctx.fillRect(0, 0, canvas.width, header);
    await document.fonts?.ready;
    ctx.fillStyle = '#FFF8EF';
    ctx.font = `700 ${56 * scale}px Caveat, cursive`;
    ctx.fillText(`${button.dataset.name}’s living library`, 36 * scale, 66 * scale);
    ctx.fillStyle = '#F2C38B';
    ctx.font = `600 ${18 * scale}px Figtree, sans-serif`;
    ctx.fillText(`${button.dataset.count} books on the tree · bookworm`, 38 * scale, 100 * scale);

    const draw = async (img) => {
        const r = img.getBoundingClientRect();
        const clip = img.closest('.book-tree__segment, .tree-book')?.getBoundingClientRect() ?? r;
        ctx.save();
        ctx.beginPath();
        ctx.rect((clip.left - box.left) * scale, (clip.top - box.top) * scale + header, clip.width * scale, clip.height * scale);
        ctx.clip();
        const source = await loadImage(img.currentSrc || img.src);
        if (source) {
            ctx.drawImage(source, (r.left - box.left) * scale, (r.top - box.top) * scale + header, r.width * scale, r.height * scale);
        }
        ctx.restore();
    };

    for (const img of frame.querySelectorAll('.book-tree__segment img')) await draw(img);
    for (const book of frame.querySelectorAll('.tree-book')) {
        const r = book.getBoundingClientRect();
        const x = (r.left - box.left) * scale;
        const y = (r.top - box.top) * scale + header;
        ctx.fillStyle = '#6D1A2B';
        ctx.fillRect(x, y, r.width * scale, r.height * scale);
        const img = book.querySelector('img');
        if (img) await draw(img);
        else {
            ctx.fillStyle = '#FFF6E3';
            ctx.font = `600 ${11 * scale}px Lora, serif`;
            const title = book.querySelector('.tree-book__fallback-title')?.textContent.trim() ?? '';
            ctx.fillText(title.slice(0, 14), x + 6 * scale, y + 20 * scale, r.width * scale - 10 * scale);
        }
    }

    canvas.toBlob(async (blob) => {
        if (!blob) return;
        const file = new File([blob], 'my-bookworm-tree.png', { type: 'image/png' });
        if (navigator.canShare?.({ files: [file] })) {
            try { await navigator.share({ files: [file], title: 'My Bookworm tree' }); return; } catch { /* fall back to download */ }
        }
        const link = document.createElement('a');
        link.href = URL.createObjectURL(blob);
        link.download = file.name;
        link.click();
        window.setTimeout(() => URL.revokeObjectURL(link.href), 2000);
    }, 'image/png');
}

document.querySelectorAll('[data-share-tree-image]').forEach((button) => {
    button.addEventListener('click', async () => {
        button.disabled = true;
        try { await renderTreeImage(button); } finally { button.disabled = false; }
    });
});
