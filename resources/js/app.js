import './bootstrap';

import Alpine from 'alpinejs';
import './pdf-reader';

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

Alpine.start();
