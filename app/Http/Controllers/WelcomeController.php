<?php

namespace App\Http\Controllers;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;
use UnexpectedValueException;

class WelcomeController extends Controller
{
    public function __invoke(): View
    {
        if (auth()->guest()) {
            return view('landing');
        }

        $quote = null;

        // The quote is cached for a day. If ZenQuotes is slow or down we
        // remember that for 30 minutes, so the home page never waits on it
        // again (it used to block every page load for up to 8 seconds).
        $quote = Cache::get('zenquotes:today');

        if ($quote === null && ! Cache::has('zenquotes:unavailable')) {
            try {
                $quotes = Http::timeout(2)->connectTimeout(2)
                    ->get('https://zenquotes.io/api/today')
                    ->throw()
                    ->json();

                $dailyQuote = $quotes[0] ?? null;

                if (! is_array($dailyQuote) || empty($dailyQuote['q']) || empty($dailyQuote['a'])) {
                    throw new UnexpectedValueException('ZenQuotes returned an invalid daily quote response.');
                }

                $quote = ['text' => $dailyQuote['q'], 'author' => $dailyQuote['a']];
                Cache::put('zenquotes:today', $quote, now()->endOfDay());
            } catch (ConnectionException|RequestException|UnexpectedValueException $exception) {
                report($exception);
                Cache::put('zenquotes:unavailable', true, now()->addMinutes(30));
            }
        }

        $quoteError = $quote === null;

        $user = auth()->user();
        $reading = $user->shelfItems()
            ->with('book')
            ->where('status', 'reading')
            ->latest('updated_at')
            ->take(4)
            ->get();
        $shelfCount = $user->shelfItems()->count();

        $streak = $user->readingStreak();
        $readToday = $user->readingSessions()->whereDate('read_on', today())->exists();
        $recommendations = array_slice(\App\Support\Recommender::for($user, includeGenres: false), 0, 1);

        return view('welcome', compact('quote', 'quoteError', 'reading', 'shelfCount', 'streak', 'readToday', 'recommendations'));
    }
}
