<?php

namespace App\Support;

use App\Models\Book;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CoverCache
{
    public const WIDTH = 320;

    public static function key(Book $book): string
    {
        return substr(md5((string) $book->cover_url), 0, 8);
    }

    public static function relativePath(Book $book): string
    {
        $ext = function_exists('imagewebp') ? 'webp' : 'jpg';

        return 'covers/' . $book->id . '-' . self::key($book) . '.' . $ext;
    }

    /** Make sure the cover is cached; returns the absolute path, or null if it couldn't be fetched. */
    public static function ensure(Book $book): ?string
    {
        $disk = Storage::disk('local');
        $relative = self::relativePath($book);

        if ($disk->exists($relative)) {
            return $disk->path($relative);
        }

        // Don't retry a cover that just failed: that would slow every page view.
        $failKey = 'cover-failed:' . $book->id . ':' . self::key($book);
        if (Cache::has($failKey)) {
            return null;
        }

        $path = self::download($book, $relative);
        if (! $path) {
            Cache::put($failKey, true, now()->addHour());
        }

        return $path;
    }

    private static function download(Book $book, string $relative): ?string
    {
        $disk = Storage::disk('local');

        try {
            $response = Http::timeout(6)->connectTimeout(3)->get($book->cover_url);
            if (! $response->successful() || ! str_starts_with((string) $response->header('Content-Type'), 'image/')) {
                return null;
            }

            $source = @imagecreatefromstring($response->body());
            if (! $source) {
                return null;
            }

            // Open Library returns a 1×1 placeholder when it has no cover.
            if (imagesx($source) < 10) {
                imagedestroy($source);

                return null;
            }

            $width = min(self::WIDTH, imagesx($source));
            $height = (int) round(imagesy($source) * $width / imagesx($source));
            $resized = imagescale($source, $width, $height, IMG_BICUBIC);

            ob_start();
            function_exists('imagewebp') ? imagewebp($resized, null, 80) : imagejpeg($resized, null, 82);
            $disk->put($relative, ob_get_clean());

            imagedestroy($source);
            imagedestroy($resized);

            return $disk->path($relative);
        } catch (Throwable $exception) {
            report($exception);

            return null;
        }
    }

    public static function mimeFor(string $path): string
    {
        return str_ends_with($path, '.webp') ? 'image/webp' : 'image/jpeg';
    }
}
