{{-- Shared <head> assets. Fonts are served by Bookworm itself (no third-party font host to wait for). --}}
<script>
    (() => {
        try {
            const saved = localStorage.getItem('bookworm-theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.classList.toggle('dark', saved ? saved === 'dark' : prefersDark);
        } catch (e) {}
    })();
</script>
@vite(['resources/css/app.css', 'resources/css/posts.css', 'resources/css/barnaby.css', 'resources/js/app.js'])
