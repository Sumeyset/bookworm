# Why Bookworm felt slow, and what changed

## Fixed in this branch

| Problem | Effect | Fix |
|---|---|---|
| `vite.config.js` was set up for the React prototype (`src/`) instead of Laravel | `npm run build` never produced `public/build`, so the Blade pages' CSS/JS were missing or served from a stale build | Restored `laravel-vite-plugin` with Laravel entry points |
| `app.js` imported the PDF reader | **Every page** downloaded pdf.js (~340 KB JS + 84 KB CSS, ~110 KB gzipped) | PDF reader is its own entry, loaded only on the reader page via `@push('scripts')` |
| `axios` bundled but never used | ~50 KB extra JS on every page | Removed. `app.js` is now 56 KB (20 KB gzipped), down from ~108 KB + pdf.js |
| Home page called ZenQuotes on the request with an 8 s timeout, and **failures were never cached** | When the API was slow or blocked, every visit to `/` waited up to 8 s | 2 s timeout, success cached for the day, failure cached for 30 min |
| Book search ran `updateOrCreate` for each of 20 results | ~40 queries per search, even when the results were cached | A single `upsert`; Open Library results are cached for 12 h |
| Profile page ran separate queries for each stat and rendered the tree from a paginated list | Extra queries, and books after #12 never showed on the tree | One query loads the shelf; stats are computed from it |
| Tree used a 200 KB background photo (plus an unused 636 KB copy) | Large image on the profile page | Tree is drawn with CSS and SVG, so no image is needed |
| Fonts: 3 families with 16 weights | Larger render-blocking font CSS | Trimmed to the 9 weights actually used |
| Compiled Blade views (`storage/framework/views`) and `bootstrap/cache/*.php` were committed | Stale compiled views/caches can be served after a pull | Untracked, and Laravel's standard `.gitignore` files restored |
| `.env.example` had every value blank | Fresh installs ran with an undefined `APP_ENV`/`APP_DEBUG`, cache and session settings | Restored sensible defaults |
| Missing indexes on `reviews (book_id, created_at)`, `reviews (user_id, created_at)`, `user_follows (followed_id)` | Feed, book page and follower counts scan tables as data grows | Added in a migration |

## Do these on the server (not code)

1. **Production mode**: `APP_ENV=production`, `APP_DEBUG=false`. Debug mode is much slower.
2. **Cache everything Laravel can**, after each deploy:
   ```bash
   composer install --no-dev --optimize-autoloader
   npm ci && npm run build
   php artisan optimize
   ```
3. **Enable OPcache** in PHP (`opcache.enable=1`, `opcache.validate_timestamps=0` in production).
4. **Don't use `php artisan serve` for real traffic.** It is a single-threaded dev server: one slow request (a PDF, a search) blocks everyone else. Use nginx + PHP-FPM, Laravel Herd/Valet locally, or at least `PHP_CLI_SERVER_WORKERS=4`.
5. **Cache/session store**: `CACHE_STORE=database` and `SESSION_DRIVER=database` write to the database on every request. Redis (or `file` on a single server) is faster.
6. **Don't run `npm run dev` on the live site.** The Vite dev server serves unbundled modules, which is fine locally but slow for visitors.
