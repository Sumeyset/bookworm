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
        $quoteError = false;

        try {
            $quote = Cache::remember('zenquotes:today', now()->addDay(), function (): array {
                $quotes = Http::timeout(8)
                    ->get('https://zenquotes.io/api/today')
                    ->throw()
                    ->json();

                $dailyQuote = $quotes[0] ?? null;

                if (! is_array($dailyQuote) || empty($dailyQuote['q']) || empty($dailyQuote['a'])) {
                    throw new UnexpectedValueException('ZenQuotes returned an invalid daily quote response.');
                }

                return [
                    'text' => $dailyQuote['q'],
                    'author' => $dailyQuote['a'],
                ];
            });
        } catch (ConnectionException|RequestException|UnexpectedValueException $exception) {
            report($exception);
            $quoteError = true;
        }

        return view('welcome', compact('quote', 'quoteError'));
    }
}
