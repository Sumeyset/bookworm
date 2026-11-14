<?php

namespace App\Support;

/**
 * The tree bookshelf picture (public/images/tree/tree-shelf.webp) and where
 * its planks are, measured in image pixels.
 *
 * The picture has 7 planks. To grow the tree taller, a horizontal band of the
 * trunk (rows A..B, holding one left and one right plank) is repeated, with
 * a soft crossfade at each seam. Each extra band adds 2 planks (6 books).
 * The browser does the stacking with CSS, so only one image is downloaded.
 */
class TreeBackdrop
{
    public const IMAGE = 'images/tree/tree-shelf.webp';

    public const WIDTH = 1678;

    public const HEIGHT = 937;

    /** The repeatable band: rows [A, B). */
    public const BAND_FROM = 452;

    public const BAND_TO = 594;

    /** Rows blended at each seam. */
    public const FADE = 36;

    /** Narrow screens crop to the tree itself: columns [CROP_FROM, CROP_TO). */
    public const CROP_FROM = 340;

    public const CROP_TO = 1300;

    /** Top edge of each plank's front face, and its left/right ends. */
    public const PLANKS = [
        ['y' => 220, 'x1' => 785, 'x2' => 1145],
        ['y' => 302, 'x1' => 428, 'x2' => 818],
        ['y' => 365, 'x1' => 797, 'x2' => 1178],
        ['y' => 462, 'x1' => 419, 'x2' => 800],
        ['y' => 515, 'x1' => 830, 'x2' => 1218],
        ['y' => 611, 'x1' => 440, 'x2' => 826],
        ['y' => 667, 'x1' => 838, 'x2' => 1196],
    ];

    /** How many shelves the picture can show for the given number needed. */
    public static function shelvesFor(int $needed): int
    {
        return count(self::PLANKS) + 2 * self::bandsFor($needed);
    }

    private static function bandsFor(int $needed): int
    {
        return max(0, (int) ceil(($needed - count(self::PLANKS)) / 2));
    }

    /**
     * @return array{width: int, height: int, segments: list<array{from: int, to: int, at: int, fade: bool}>, shelves: array<int, array{left: float, width: float, bottom: float}>}
     */
    public static function layout(int $shelfCount): array
    {
        $bands = self::bandsFor($shelfCount);
        $step = self::BAND_TO - self::BAND_FROM;
        $height = self::HEIGHT + $bands * $step;

        $planks = [];
        foreach (self::PLANKS as $plank) {
            if ($plank['y'] < self::BAND_TO) {
                $planks[] = $plank;
            } else {
                $planks[] = ['y' => $plank['y'] + $bands * $step] + $plank;
            }
            if ($plank['y'] >= self::BAND_FROM && $plank['y'] < self::BAND_TO) {
                for ($i = 1; $i <= $bands; $i++) {
                    $planks[] = ['y' => $plank['y'] + $i * $step] + $plank;
                }
            }
        }

        // Slot 0 is the lowest plank.
        usort($planks, fn ($a, $b) => $b['y'] <=> $a['y']);

        $shelves = [];
        foreach ($planks as $slot => $plank) {
            $shelves[$slot] = [
                'left' => round($plank['x1'] / self::WIDTH * 100, 3),
                'width' => round(($plank['x2'] - $plank['x1']) / self::WIDTH * 100, 3),
                'bottom' => round(($height - $plank['y']) / $height * 100, 3),
            ];
        }

        $segments = [['from' => 0, 'to' => self::BAND_TO, 'at' => 0, 'fade' => false]];
        for ($i = 0; $i < $bands; $i++) {
            $segments[] = [
                'from' => self::BAND_FROM - self::FADE,
                'to' => self::BAND_TO,
                'at' => self::BAND_TO + $i * $step - self::FADE,
                'fade' => true,
            ];
        }
        $segments[] = [
            'from' => self::BAND_TO - self::FADE,
            'to' => self::HEIGHT,
            'at' => self::BAND_TO + $bands * $step - self::FADE,
            'fade' => true,
        ];

        return [
            'width' => self::WIDTH,
            'height' => $height,
            'segments' => $segments,
            'shelves' => $shelves,
        ];
    }
}
