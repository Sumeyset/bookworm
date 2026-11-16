// Reading room: shared countdown, who's here, and a quiet chat (polled every 5 seconds).
const root = document.querySelector('[data-room]');
if (root) {
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const clock = root.querySelector('[data-room-clock]');
    const ring = root.querySelector('[data-room-ring]');
    const people = root.querySelector('[data-room-members]');
    const chat = root.querySelector('[data-room-chat]');
    const form = root.querySelector('[data-room-form]');
    const length = 2 * Math.PI * 54;
    let ends = new Date(root.dataset.ends).getTime();
    const total = Math.max(1, ends - Date.now());
    let after = 0;

    const tick = () => {
        const left = Math.max(0, ends - Date.now());
        const m = Math.floor(left / 60000);
        const s = Math.floor((left % 60000) / 1000);
        clock.textContent = `${m}:${String(s).padStart(2, '0')}`;
        ring.style.strokeDasharray = `${length}`;
        ring.style.strokeDashoffset = `${length * (1 - left / total)}`;
        if (left === 0) {
            root.querySelector('[data-room-done]').hidden = false;
            root.querySelector('[data-room-note]').hidden = true;
            if (form) form.hidden = true;
        }
    };

    const el = (tag, cls, text) => { const e = document.createElement(tag); if (cls) e.className = cls; if (text !== undefined) e.textContent = text; return e; };

    const render = (state) => {
        ends = new Date(state.ends_at).getTime();
        people.replaceChildren(...state.members.map((m) => {
            const li = el('li', `room__person ${m.here ? 'is-here' : ''}`);
            const avatar = m.photo ? Object.assign(el('img', 'room__avatar'), { src: m.photo, alt: '' }) : el('span', 'room__avatar', m.initial);
            const text = el('span', 'min-w-0 flex-1');
            text.append(el('b', 'block truncate', m.name), el('small', 'block truncate', m.book ? `📖 ${m.book.title}` : 'Reading'));
            li.append(avatar, text);
            return li;
        }));
        state.messages.forEach((msg) => {
            const line = el('p', `room__msg ${msg.mine ? 'is-mine' : ''}`);
            line.append(el('b', '', `${msg.name} `), document.createTextNode(msg.body), el('time', '', ` ${msg.time}`));
            chat.append(line);
            after = Math.max(after, msg.id);
        });
        if (state.messages.length) chat.scrollTop = chat.scrollHeight;
    };

    const poll = async () => {
        try {
            const response = await fetch(`${root.dataset.stateUrl}?after=${after}`, { headers: { Accept: 'application/json' }, credentials: 'same-origin' });
            if (response.ok) render(await response.json());
        } catch {
            // offline for a moment
        }
    };

    form?.addEventListener('submit', async (event) => {
        event.preventDefault();
        const input = form.querySelector('input[name=body]');
        const body = input.value.trim();
        if (!body) return;
        input.value = '';
        const response = await fetch(`${root.dataset.messageUrl}?after=${after}`, {
            method: 'POST', credentials: 'same-origin',
            headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': csrf },
            body: JSON.stringify({ body }),
        });
        if (response.ok) render(await response.json());
    });

    tick();
    poll();
    window.setInterval(tick, 1000);
    window.setInterval(() => { if (!document.hidden) poll(); }, 5000);
}
