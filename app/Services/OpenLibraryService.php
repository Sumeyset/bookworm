<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class OpenLibraryService
{
    private const FIELDS = 'key,title,author_name,first_publish_year,cover_i';

    public function search(string $query): array
    {
        $cacheKey = 'ol:search:' . md5(strtolower($query));

        return Cache::remember($cacheKey, 3600, function () use ($query) {
            $response = Http::timeout(8)->get('https://openlibrary.org/search.json', [
                'q' => $query,
                'limit' => 20,
                'fields' => self::FIELDS,
            ])->throw();

            return collect($response->json('docs', []))
                ->map(fn (array $doc) => $this->format($doc))
                ->all();
        });
    }

    private function format(array $doc): array
    {
        return [
            'ol_id' => str_replace('/works/', '', $doc['key'] ?? ''),
            'title' => $doc['title'] ?? 'Untitled',
            'author_name' => $doc['author_name'][0] ?? null,
            'first_publish_year' => $doc['first_publish_year'] ?? null,
            'cover_url' => isset($doc['cover_i'])
                ? "https://covers.openlibrary.org/b/id/{$doc['cover_i']}-M.jpg"
                : null,
        ];
    }
}
