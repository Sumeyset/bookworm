<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Profile photos are shown small, so they're stored small: a square-ish
 * image at most 512px, as WebP. A 4 MB phone photo becomes ~40 KB.
 */
class Avatar
{
    public const MAX = 512;

    /** Store an uploaded photo resized; returns the public-disk path. */
    public static function store(UploadedFile $file): string
    {
        $source = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));
        if (! $source || ! function_exists('imagewebp')) {
            // Can't read it here: keep the original rather than lose it.
            return $file->store('profile-photos', 'public') ?: throw new \RuntimeException('Failed to store the uploaded profile photo.');
        }

        $width = imagesx($source);
        $height = imagesy($source);
        $scale = min(1, self::MAX / max($width, $height));
        $target = imagecreatetruecolor(max(1, (int) round($width * $scale)), max(1, (int) round($height * $scale)));
        imagealphablending($target, false);
        imagesavealpha($target, true);
        imagecopyresampled($target, $source, 0, 0, 0, 0, imagesx($target), imagesy($target), $width, $height);

        ob_start();
        imagewebp($target, null, 82);
        $bytes = ob_get_clean();
        imagedestroy($source);
        imagedestroy($target);

        $path = 'profile-photos/' . Str::random(40) . '.webp';
        Storage::disk('public')->put($path, $bytes) ?: throw new \RuntimeException('Failed to store the uploaded profile photo.');

        return $path;
    }
}
