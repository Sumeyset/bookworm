// Live reader search, Instagram style: the top five readers appear as you type.

const cache = new Map();

export async function fetchReaders(query, signal) {
    const key = query.trim().toLowerCase();
    if (!key) return [];
    if (cache.has(key)) return cache.get(key);
    const response = await fetch(`/users/suggest?q=${encodeURIComponent(key)}`, {
        credentials: 'same-origin',
        headers: { Accept: 'application/json' },
        signal,
    });
    if (!response.ok) throw new Error('search failed');
    const readers = await response.json();
    cache.set(key, readers);
    return readers;
}

export function readerAvatar(reader) {
    if (reader.photo) {
        const image = document.createElement('img');
        image.src = reader.photo;
        image.alt = '';
        image.className = 'reader-suggest__avatar';
        return image;
    }
    const initial = document.createElement('span');
    initial.className = 'reader-suggest__avatar reader-suggest__avatar--initial';
    initial.textContent = (reader.name || reader.username || '?').charAt(0).toUpperCase();
    return initial;
}

export function readerMeta(reader) {
    return [
        reader.name,
        `${reader.followers} ${reader.followers === 1 ? 'follower' : 'followers'}`,
        reader.following ? 'Following' : null,
    ].filter(Boolean).join(' · ');
}

// Highlight the typed letters inside a name.
export function markMatch(text, query) {
    const fragment = document.createDocumentFragment();
    const index = text.toLowerCase().indexOf(query.toLowerCase().replace(/^@/, ''));
    if (index < 0 || !query) {
        fragment.append(text);
        return fragment;
    }
    const length = query.replace(/^@/, '').length;
    const mark = document.createElement('mark');
    mark.textContent = text.slice(index, index + length);
    fragment.append(text.slice(0, index), mark, text.slice(index + length));
    return fragment;
}

/** Attach a dropdown of live results under a search input. */
export function attachReaderSuggest(input) {
    const box = document.createElement('div');
    box.className = 'reader-suggest';
    box.id = `${input.id || 'reader-search'}-suggestions`;
    box.setAttribute('role', 'listbox');
    box.hidden = true;
    input.parentElement.style.position = 'relative';
    input.parentElement.append(box);
    input.setAttribute('role', 'combobox');
    input.setAttribute('aria-autocomplete', 'list');
    input.setAttribute('aria-controls', box.id);
    input.setAttribute('aria-expanded', 'false');

    let controller;
    let timer;
    let active = -1;

    const options = () => [...box.querySelectorAll('[role="option"]')];
    const setActive = (index) => {
        const list = options();
        if (!list.length) return;
        active = (index + list.length) % list.length;
        list.forEach((option, i) => option.classList.toggle('is-active', i === active));
        input.setAttribute('aria-activedescendant', list[active].id);
    };
    const close = () => {
        box.hidden = true;
        active = -1;
        input.setAttribute('aria-expanded', 'false');
        input.removeAttribute('aria-activedescendant');
    };

    const render = (readers, query) => {
        box.replaceChildren();
        if (!readers.length) {
            const empty = document.createElement('p');
            empty.className = 'reader-suggest__empty';
            empty.textContent = `No readers match “${query}”.`;
            box.append(empty);
        }
        readers.forEach((reader, index) => {
            const link = document.createElement('a');
            link.href = reader.url;
            link.id = `${box.id}-${index}`;
            link.className = 'reader-suggest__item';
            link.setAttribute('role', 'option');
            const text = document.createElement('span');
            text.className = 'reader-suggest__text';
            const username = document.createElement('b');
            username.append(markMatch(reader.username, query));
            const meta = document.createElement('small');
            meta.textContent = readerMeta(reader);
            text.append(username, meta);
            link.append(readerAvatar(reader), text);
            box.append(link);
        });
        const all = document.createElement('a');
        all.href = `${input.form?.action || '/users'}?q=${encodeURIComponent(query)}`;
        all.id = `${box.id}-all`;
        all.className = 'reader-suggest__all';
        all.setAttribute('role', 'option');
        all.textContent = `See all results for “${query}”`;
        box.append(all);
        box.hidden = false;
        input.setAttribute('aria-expanded', 'true');
        active = -1;
    };

    const search = () => {
        const query = input.value.trim();
        controller?.abort();
        if (!query) {
            close();
            return;
        }
        controller = new AbortController();
        fetchReaders(query, controller.signal)
            .then((readers) => {
                if (input.value.trim() === query) render(readers, query);
            })
            .catch(() => {});
    };

    input.addEventListener('input', () => {
        window.clearTimeout(timer);
        timer = window.setTimeout(search, 120);
    });
    input.addEventListener('focus', () => {
        if (input.value.trim()) search();
    });
    input.addEventListener('keydown', (event) => {
        if (box.hidden) return;
        if (event.key === 'ArrowDown') { event.preventDefault(); setActive(active + 1); }
        if (event.key === 'ArrowUp') { event.preventDefault(); setActive(active - 1); }
        if (event.key === 'Escape') close();
        if (event.key === 'Enter' && active >= 0) {
            event.preventDefault();
            options()[active]?.click();
        }
    });
    document.addEventListener('click', (event) => {
        if (!box.contains(event.target) && event.target !== input) close();
    });
}
