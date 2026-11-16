// Year in Books story: tap (or swipe, or use the arrow keys) through full-screen slides.
const SLIDE_MS = 5500;

function initStory(story) {
    const slides = [...story.querySelectorAll('.year-story__slide')];
    const bars = story.querySelector('.year-story__bars');
    bars.innerHTML = slides.map(() => '<span><i></i></span>').join('');
    const fills = [...bars.querySelectorAll('i')];
    let index = 0;
    let timer = null;
    let started = 0;
    let paused = false;
    let lastFocus = null;

    const countUp = (slide) => {
        slide.querySelectorAll('[data-count-to]').forEach((el) => {
            const target = Number(el.dataset.countTo) || 0;
            const begin = performance.now();
            const step = (now) => {
                const t = Math.min(1, (now - begin) / 1100);
                el.textContent = Math.round(target * (1 - (1 - t) ** 3)).toLocaleString();
                if (t < 1 && slide.classList.contains('is-active')) requestAnimationFrame(step);
            };
            requestAnimationFrame(step);
        });
    };

    const show = (i) => {
        index = Math.max(0, Math.min(slides.length - 1, i));
        slides.forEach((slide, n) => slide.classList.toggle('is-active', n === index));
        fills.forEach((fill, n) => {
            fill.style.transition = 'none';
            fill.style.width = n < index ? '100%' : '0%';
        });
        countUp(slides[index]);
        restart();
    };

    const restart = () => {
        window.clearTimeout(timer);
        if (index === slides.length - 1) {
            fills[index].style.width = '100%';
            return; // stay on the last slide
        }
        const fill = fills[index];
        requestAnimationFrame(() => {
            fill.style.transition = `width ${SLIDE_MS}ms linear`;
            fill.style.width = '100%';
        });
        started = performance.now();
        timer = window.setTimeout(() => show(index + 1), SLIDE_MS);
    };

    const open = () => {
        lastFocus = document.activeElement;
        story.hidden = false;
        document.documentElement.classList.add('overflow-hidden');
        show(0);
        story.querySelector('[data-story-next]').focus();
    };
    const close = () => {
        window.clearTimeout(timer);
        story.hidden = true;
        document.documentElement.classList.remove('overflow-hidden');
        lastFocus?.focus();
    };

    document.querySelectorAll('[data-story-open]').forEach((button) => button.addEventListener('click', open));
    story.querySelector('[data-story-close]').addEventListener('click', close);
    story.querySelector('[data-story-next]').addEventListener('click', () => (index === slides.length - 1 ? null : show(index + 1)));
    story.querySelector('[data-story-back]').addEventListener('click', () => show(index - 1));
    story.querySelector('[data-story-replay]')?.addEventListener('click', () => show(0));
    story.querySelector('[data-story-card]')?.addEventListener('click', () => document.querySelector('[data-recap-card]')?.click());
    story.querySelector('[data-story-share]')?.addEventListener('click', async (event) => {
        const { url, text } = event.currentTarget.dataset;
        try {
            if (navigator.share) await navigator.share({ title: document.title, text, url });
            else {
                await navigator.clipboard.writeText(url);
                event.currentTarget.textContent = 'Link copied ✓';
            }
        } catch {
            // share sheet closed
        }
    });

    // Hold to pause, like stories everywhere.
    const pause = () => {
        if (paused || story.hidden) return;
        paused = true;
        window.clearTimeout(timer);
        const fill = fills[index];
        const done = Math.min(1, (performance.now() - started) / SLIDE_MS);
        fill.style.transition = 'none';
        fill.style.width = `${done * 100}%`;
        fill.dataset.left = String((1 - done) * SLIDE_MS);
    };
    const resume = () => {
        if (!paused) return;
        paused = false;
        if (index === slides.length - 1) return;
        const left = Number(fills[index].dataset.left) || SLIDE_MS;
        const fill = fills[index];
        requestAnimationFrame(() => {
            fill.style.transition = `width ${left}ms linear`;
            fill.style.width = '100%';
        });
        started = performance.now() - (SLIDE_MS - left);
        timer = window.setTimeout(() => show(index + 1), left);
    };
    story.addEventListener('pointerdown', (event) => {
        if (event.target.closest('button:not(.year-story__tap)')) return;
        pause();
    });
    story.addEventListener('pointerup', resume);
    story.addEventListener('pointercancel', resume);

    // Swipe down to close.
    let startY = null;
    story.addEventListener('touchstart', (event) => { startY = event.touches[0].clientY; }, { passive: true });
    story.addEventListener('touchend', (event) => {
        if (startY !== null && event.changedTouches[0].clientY - startY > 120) close();
        startY = null;
    });

    document.addEventListener('keydown', (event) => {
        if (story.hidden) return;
        if (event.key === 'Escape') close();
        if (event.key === 'ArrowRight') show(index + 1);
        if (event.key === 'ArrowLeft') show(index - 1);
    });

    // Open straight away from a link like /u/name/year/2026#story
    if (window.location.hash === '#story') open();
}

document.querySelectorAll('[data-year-story]').forEach(initStory);
