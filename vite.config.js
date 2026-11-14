import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

const codespaceHost = process.env.CODESPACE_NAME && process.env.GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN
    ? `${process.env.CODESPACE_NAME}-5173.${process.env.GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN}`
    : null;

export default defineConfig({
    plugins: [
        laravel({
            // Each page only loads what it needs:
            //  - app.js / app.css   → every page (small: Alpine + theme toggle)
            //  - tree-shelf.js      → the bookshelf tree (drag & drop + book popup)
            //  - pdf-reader.js      → only the PDF reader page (pdf.js is ~1 MB)
            //  - reader.css         → reader styles, linked in <head> so the page never shows unstyled
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/tree-shelf.js',
                'resources/js/pdf-reader.js',
                'resources/css/reader.css',
            ],
            refresh: true,
        }),
    ],
    build: {
        // pdf.js is intentionally large and isolated in its own chunk.
        chunkSizeWarningLimit: 1200,
    },
    ...(codespaceHost && {
        server: {
            host: '0.0.0.0',
            origin: `https://${codespaceHost}`,
            allowedHosts: [codespaceHost],
            hmr: {
                protocol: 'wss',
                host: codespaceHost,
                clientPort: 443,
            },
        },
    }),
});
