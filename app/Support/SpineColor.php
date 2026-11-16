<?php

namespace App\Support;

use App\Models\Book;
use Illuminate\Support\Facades\Storage;

/**
 * The colour a book's spine should be, read from its cover: the most common
 * strong colour on the cover, plus a text colour that reads well on it.
 * Worked out once (from the cached cover) and saved on the book.
 */
class SpineColor
{
    /** @return array{0: string, 1: string}|null [background, text], or null when there's no cached cover yet */
    public static function for(Book $book): ?array
    {
        if ($book->spine_color) {
            return [$book->spine_color, $book->spine_text ?: '#FFF4DF'];
        }
        if (! $book->cover_url) {
            return null;
        }
        $relative = CoverCache::relativePath($book);
        if (! Storage::disk('local')->exists($relative)) {
            return null; // the cover is fetched the first time it's shown; next visit gets the colour
        }

        $colors = self::fromImage(Storage::disk('local')->path($relative));
        if ($colors) {
            $book->forceFill(['spine_color' => $colors[0], 'spine_text' => $colors[1]])->saveQuietly();
        }

        return $colors;
    }

    /** @return array{0: string, 1: string}|null */
    public static function fromImage(string $path): ?array
    {
        $image = @imagecreatefromstring((string) file_get_contents($path));
        if (! $image) {
            return null;
        }
        $small = imagescale($image, 24, 36, IMG_BILINEAR_FIXED);
        imagedestroy($image);

        // Count colours in coarse buckets; strong colours count extra, so a
        // big white margin doesn't win over the cover's real colour.
        $buckets = [];
        for ($y = 0; $y < 36; $y++) {
            for ($x = 0; $x < 24; $x++) {
                $rgb = imagecolorat($small, $x, $y);
                $r = ($rgb >> 16) & 255;
                $g = ($rgb >> 8) & 255;
                $b = $rgb & 255;
                $max = max($r, $g, $b);
                $min = min($r, $g, $b);
                $saturation = $max === 0 ? 0 : ($max - $min) / $max;
                $weight = 1 + $saturation * 2.5 - (($max > 235 && $saturation < .12) ? .7 : 0);
                $key = ($r >> 5) . '-' . ($g >> 5) . '-' . ($b >> 5);
                $buckets[$key]['w'] = ($buckets[$key]['w'] ?? 0) + $weight;
                $buckets[$key]['r'] = ($buckets[$key]['r'] ?? 0) + $r;
                $buckets[$key]['g'] = ($buckets[$key]['g'] ?? 0) + $g;
                $buckets[$key]['b'] = ($buckets[$key]['b'] ?? 0) + $b;
                $buckets[$key]['n'] = ($buckets[$key]['n'] ?? 0) + 1;
            }
        }
        imagedestroy($small);
        uasort($buckets, fn ($a, $b) => $b['w'] <=> $a['w']);
        $top = reset($buckets);
        [$r, $g, $b] = [(int) ($top['r'] / $top['n']), (int) ($top['g'] / $top['n']), (int) ($top['b'] / $top['n'])];

        $luminance = (0.2126 * $r + 0.7152 * $g + 0.0722 * $b) / 255;

        return [sprintf('#%02X%02X%02X', $r, $g, $b), $luminance > .58 ? '#2A1A1D' : '#FFF4DF'];
    }
}
