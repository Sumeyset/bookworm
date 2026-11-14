<?php

namespace App\Providers;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Short, stable names for likes/comments instead of class names.
        Relation::morphMap([
            'post' => \App\Models\Post::class,
            'review' => \App\Models\Review::class,
            'user' => \App\Models\User::class,
        ]);

        // Rate limits: keep searches, posting and imports from being abused.
        $key = fn (Request $request) => $request->user()?->id ?: $request->ip();
        RateLimiter::for('search', fn (Request $request) => Limit::perMinute(40)->by($key($request)));
        RateLimiter::for('posting', fn (Request $request) => Limit::perMinute(20)->by($key($request)));
        RateLimiter::for('reactions', fn (Request $request) => Limit::perMinute(90)->by($key($request)));
        RateLimiter::for('import', fn (Request $request) => Limit::perHour(10)->by($key($request)));

        if (env('CODESPACE_NAME')) {
            URL::forceRootUrl('https://'.env('CODESPACE_NAME').'-8000.'.env('GITHUB_CODESPACES_PORT_FORWARDING_DOMAIN'));
            URL::forceScheme('https');
        }
    }
}
