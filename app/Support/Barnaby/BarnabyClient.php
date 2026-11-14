<?php

namespace App\Support\Barnaby;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * Talks to the AI providers. Tries each configured provider in order and
 * falls back to the next on any failure (no key, rate limit, outage, empty reply).
 */
class BarnabyClient
{
    public static function configured(): bool
    {
        return collect(config('barnaby.providers'))->contains(fn ($provider) => filled(config("barnaby.$provider.key")));
    }

    /**
     * @param  array<int, array{role: string, text: string}>  $turns  user/assistant turns, oldest first
     */
    public function reply(string $system, array $turns, float $temperature = 0.7, int $maxTokens = 700): string
    {
        $tried = [];
        foreach (config('barnaby.providers') as $provider) {
            if (blank(config("barnaby.$provider.key")) || ! method_exists($this, $provider)) {
                continue;
            }
            $tried[] = $provider;
            try {
                $text = trim($this->{$provider}($system, $turns, $temperature, $maxTokens));
                if ($text !== '') {
                    return $text;
                }
                Log::warning("Barnaby: $provider returned an empty reply.");
            } catch (RequestException $exception) {
                Log::warning("Barnaby: $provider failed with HTTP {$exception->response->status()}.");
            } catch (ConnectionException $exception) {
                Log::warning("Barnaby: could not reach $provider: {$exception->getMessage()}");
            }
        }

        throw new BarnabyUnavailable($tried === [] ? 'no-keys' : 'all-failed');
    }

    private function gemini(string $system, array $turns, float $temperature, int $maxTokens): string
    {
        $model = config('barnaby.gemini.model');
        $response = Http::timeout(config('barnaby.timeout'))
            ->connectTimeout(5)
            ->withHeaders(['x-goog-api-key' => config('barnaby.gemini.key')])
            ->post(rtrim(config('barnaby.gemini.url'), '/') . "/models/{$model}:generateContent", [
                'systemInstruction' => ['parts' => [['text' => $system]]],
                'contents' => collect($turns)->map(fn ($turn) => [
                    'role' => $turn['role'] === 'assistant' ? 'model' : 'user',
                    'parts' => [['text' => $turn['text']]],
                ])->values()->all(),
                'generationConfig' => ['temperature' => $temperature, 'maxOutputTokens' => $maxTokens],
            ])
            ->throw();

        return collect($response->json('candidates.0.content.parts', []))->pluck('text')->filter()->implode('');
    }

    private function groq(string $system, array $turns, float $temperature, int $maxTokens): string
    {
        $response = Http::timeout(config('barnaby.timeout'))
            ->connectTimeout(5)
            ->withToken(config('barnaby.groq.key'))
            ->post(rtrim(config('barnaby.groq.url'), '/') . '/chat/completions', [
                'model' => config('barnaby.groq.model'),
                'messages' => collect([['role' => 'system', 'content' => $system]])
                    ->concat(collect($turns)->map(fn ($turn) => [
                        'role' => $turn['role'] === 'assistant' ? 'assistant' : 'user',
                        'content' => $turn['text'],
                    ]))
                    ->values()
                    ->all(),
                'temperature' => $temperature,
                'max_tokens' => $maxTokens,
            ])
            ->throw();

        return (string) $response->json('choices.0.message.content', '');
    }
}
