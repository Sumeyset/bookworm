<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#4A1220">
    <title>Offline · Bookworm</title>
    <link rel="icon" type="image/png" href="/images/app/icon-192.png">
    <style>
        :root { color-scheme: light dark; }
        body { margin: 0; min-height: 100vh; display: grid; place-items: center; background: #FFF8EF; color: #2a1a1d; font: 16px/1.5 system-ui, -apple-system, "Segoe UI", sans-serif; }
        main { width: min(30rem, 100% - 3rem); text-align: center; }
        img { width: 96px; height: 96px; border-radius: 22px; box-shadow: 0 18px 40px -18px rgb(74 18 32 / 60%); }
        h1 { margin: 1.25rem 0 .25rem; font: 700 2rem/1.1 Georgia, serif; }
        p { margin: 0; color: #7a5a48; }
        ul { margin: 1.5rem 0 0; padding: 0; list-style: none; text-align: left; }
        li a { display: block; margin-top: .5rem; padding: .85rem 1rem; border-radius: 1rem; background: #fff; color: #4A1220; font-weight: 600; text-decoration: none; box-shadow: 0 1px 0 rgb(184 137 59 / 30%); }
        button { margin-top: 1.5rem; padding: .7rem 1.4rem; border: 0; border-radius: 999px; background: #6D1A2B; color: #FFF8EF; font: inherit; font-weight: 700; cursor: pointer; }
        @media (prefers-color-scheme: dark) {
            body { background: #1a0f12; color: #f4e9e2; }
            p { color: #c7a491; }
            li a { background: #2a1d20; color: #f0a8b4; box-shadow: none; }
        }
    </style>
</head>
<body>
    <main>
        <img src="/images/app/icon-192.png" alt="">
        <h1>You’re offline</h1>
        <p>Bookworm needs the internet for most things — but books you’ve opened in the reader can still be read.</p>
        <ul data-offline-books></ul>
        <button type="button" onclick="location.reload()">Try again</button>
    </main>
    <script>
        // Books opened in the reader on this device (saved by the reader page).
        try {
            const books = JSON.parse(localStorage.getItem('bookworm-offline-books') || '[]');
            const list = document.querySelector('[data-offline-books]');
            books.forEach((book) => {
                const item = document.createElement('li');
                const link = document.createElement('a');
                link.href = book.url;
                link.textContent = '📖 ' + book.title;
                item.append(link);
                list.append(item);
            });
        } catch {}
    </script>
</body>
</html>
