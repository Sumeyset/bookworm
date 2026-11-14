{{-- Shared <head> assets. Only the font weights the design actually uses are requested. --}}
<link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
<link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700|lora:500,600,700,500i&display=swap" rel="stylesheet">
<script>
    (() => {
        try {
            const saved = localStorage.getItem('bookworm-theme');
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            document.documentElement.classList.toggle('dark', saved ? saved === 'dark' : prefersDark);
        } catch (e) {}
    })();
</script>
@vite(['resources/css/app.css', 'resources/js/app.js'])
