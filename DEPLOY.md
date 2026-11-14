# Deploying Bookworm

A checklist for putting Bookworm on a real server. See `PERFORMANCE.md` for why each setting matters.

## 1. Server

- PHP 8.2+ with the `gd` (WebP support recommended), `pdo_sqlite` or `pdo_mysql`, `mbstring` and `fileinfo` extensions
- Composer, Node 20+
- nginx + PHP-FPM (don't use `php artisan serve` for real traffic)
- OPcache on: `opcache.enable=1`, and `opcache.validate_timestamps=0` in production

## 2. Environment (`.env`)

```dotenv
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-domain.example

# Faster than the database drivers for busy sites
CACHE_STORE=redis      # or "file" on a single server
SESSION_DRIVER=redis   # or "file"

SESSION_SECURE_COOKIE=true
```

## 3. Deploy steps (every release)

```bash
git pull
composer install --no-dev --optimize-autoloader
npm ci
npm run build
php artisan migrate --force
php artisan storage:link
php artisan optimize
```

Make sure `public/hot` does **not** exist on the server; it makes pages load assets from a dev server.

## 4. Scheduler (backups and covers)

Add one cron entry for the web user:

```cron
* * * * * cd /path/to/bookworm && php artisan schedule:run >> /dev/null 2>&1
```

This runs, every day:

- `backup:database`: copies the database to `storage/app/backups` and keeps the newest 14. Copy that folder off the server too (e.g. to S3 or another machine).
- `covers:warm`: downloads and stores book covers locally so pages don't wait on Open Library.

## 5. Built-in protection

- Login is limited to 5 attempts per minute (Laravel Breeze).
- Rate limits (`app/Providers/AppServiceProvider.php`): book search 40/min, posts and replies 20/min, likes 90/min, imports 10/hour.
- Uploads: PDFs max 20 MB each, imports max 5 MB, profile photos max 5 MB.

## 6. Continuous integration

`.github/workflows/tests.yml` runs the full test suite on every pull request and on pushes to `main`.
