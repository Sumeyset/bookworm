import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';

const codespaceHost = process.env.CODESPACE_NAME && process.env.GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN
    ? `${process.env.CODESPACE_NAME}-5173.${process.env.GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN}`
    : null;

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
    ],
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
