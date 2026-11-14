<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Support\CoverCache;
use Symfony\Component\HttpFoundation\Response;

/**
 * Serves book covers from our own storage. The first request downloads the
 * cover from Open Library, shrinks it and saves it as WebP; after that it is
 * a fast local file with a one-year browser cache.
 */
class CoverController extends Controller
{
    public function show(Book $book): Response
    {
        abort_unless($book->cover_url, 404);

        $path = CoverCache::ensure($book);

        if (! $path) {
            // Open Library is unreachable right now: fall back to their URL.
            return redirect()->away($book->cover_url)->header('Cache-Control', 'public, max-age=300');
        }

        return response()->file($path, [
            'Content-Type' => CoverCache::mimeFor($path),
            'Cache-Control' => 'public, max-age=31536000, immutable',
        ]);
    }
}
