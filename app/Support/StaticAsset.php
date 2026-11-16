<?php

namespace App\Support;

/**
 * URL for a file in public/ with its version in it (?v=<last changed>).
 * Browsers and the app's offline cache keep images a long time; when a
 * picture is replaced, the new version gets a new URL and shows at once.
 */
class StaticAsset
{
    private static array $versions = [];

    public static function url(string $path): string
    {
        $path = ltrim($path, '/');
        $version = self::$versions[$path] ??= (string) (@filemtime(public_path($path)) ?: '0');

        return asset($path) . '?v=' . $version;
    }

    /** One version for a whole folder (the newest file in it), for URLs built in the browser. */
    public static function folderVersion(string $folder): string
    {
        $key = 'dir:' . trim($folder, '/');

        return self::$versions[$key] ??= (string) collect(glob(public_path(trim($folder, '/') . '/*')) ?: [])->map(fn ($file) => @filemtime($file) ?: 0)->max();
    }
}
