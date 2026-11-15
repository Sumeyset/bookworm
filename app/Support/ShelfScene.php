<?php

namespace App\Support;

/**
 * Photo bookshelves (public/images/shelves). Each scene lists where its
 * shelves are in the picture, in image pixels: books stand on the line y,
 * between x1 and x2. Slot 0 is the first shelf books fill.
 *
 *   h      tallest book that fits there (image px)
 *   align  where books gather on the shelf: start | center | end
 */
class ShelfScene
{
    public const SCENES = [
        // Christmas tree bookshelf by the fireplace. Books stand as spines.
        'christmas' => [
            'image' => 'images/shelves/christmas-tree-shelf.webp',
            'width' => 1240,
            'height' => 1116,
            'view' => 'spines',
            'spine' => 17,          // spine thickness (px)
            'min_width' => 640,     // narrow screens scroll sideways from this width
            'slots' => [
                ['x1' => 540, 'x2' => 612, 'y' => 276, 'h' => 82, 'align' => 'end'],
                ['x1' => 628, 'x2' => 700, 'y' => 276, 'h' => 82, 'align' => 'start'],
                ['x1' => 500, 'x2' => 612, 'y' => 428, 'h' => 112, 'align' => 'end'],
                ['x1' => 628, 'x2' => 745, 'y' => 428, 'h' => 112, 'align' => 'start'],
                ['x1' => 446, 'x2' => 612, 'y' => 575, 'h' => 118, 'align' => 'end'],
                ['x1' => 628, 'x2' => 798, 'y' => 575, 'h' => 118, 'align' => 'start'],
                ['x1' => 415, 'x2' => 505, 'y' => 717, 'h' => 56, 'align' => 'center'],
                ['x1' => 540, 'x2' => 616, 'y' => 717, 'h' => 108, 'align' => 'end'],
                ['x1' => 625, 'x2' => 700, 'y' => 717, 'h' => 108, 'align' => 'start'],
                ['x1' => 735, 'x2' => 825, 'y' => 717, 'h' => 56, 'align' => 'center'],
                ['x1' => 350, 'x2' => 470, 'y' => 861, 'h' => 66, 'align' => 'center'],
                ['x1' => 490, 'x2' => 616, 'y' => 861, 'h' => 112, 'align' => 'center'],
                ['x1' => 625, 'x2' => 750, 'y' => 861, 'h' => 112, 'align' => 'center'],
                ['x1' => 772, 'x2' => 892, 'y' => 861, 'h' => 66, 'align' => 'center'],
            ],
        ],

        // Plain wooden wall shelves. Books face out, so their covers show.
        'wall' => [
            'image' => 'images/shelves/wall-shelf.webp',
            'width' => 1800,
            'height' => 1200,
            'view' => 'covers',
            'book' => 128,          // cover width (px)
            'min_width' => 860,
            'slots' => [
                ['x1' => 70, 'x2' => 590, 'y' => 271, 'h' => 230, 'align' => 'center'],
                ['x1' => 640, 'x2' => 1160, 'y' => 271, 'h' => 230, 'align' => 'center'],
                ['x1' => 1210, 'x2' => 1730, 'y' => 271, 'h' => 230, 'align' => 'center'],
                ['x1' => 70, 'x2' => 590, 'y' => 554, 'h' => 230, 'align' => 'center'],
                ['x1' => 640, 'x2' => 1160, 'y' => 554, 'h' => 230, 'align' => 'center'],
                ['x1' => 1210, 'x2' => 1730, 'y' => 554, 'h' => 230, 'align' => 'center'],
                ['x1' => 70, 'x2' => 590, 'y' => 827, 'h' => 230, 'align' => 'center'],
                ['x1' => 640, 'x2' => 1160, 'y' => 827, 'h' => 230, 'align' => 'center'],
                ['x1' => 1210, 'x2' => 1730, 'y' => 827, 'h' => 230, 'align' => 'center'],
                ['x1' => 70, 'x2' => 590, 'y' => 1082, 'h' => 230, 'align' => 'center'],
                ['x1' => 640, 'x2' => 1160, 'y' => 1082, 'h' => 230, 'align' => 'center'],
                ['x1' => 1210, 'x2' => 1730, 'y' => 1082, 'h' => 230, 'align' => 'center'],
            ],
        ],

        // Built-in shelves around a window seat. The view outside changes
        // with the time of day and the season.
        'window' => [
            'image' => 'images/shelves/window-nook.webp',
            'width' => 2048,
            'height' => 1144,
            'view' => 'spines',
            'spine' => 26,
            'min_width' => 980,
            // the glass, so the view can sit behind it
            'window' => ['x1' => 676, 'y1' => 272, 'x2' => 1382, 'y2' => 736],
            'slots' => [
                ['x1' => 150, 'x2' => 326, 'y' => 222, 'h' => 112, 'align' => 'start'],
                ['x1' => 336, 'x2' => 512, 'y' => 222, 'h' => 112, 'align' => 'end'],
                ['x1' => 1546, 'x2' => 1722, 'y' => 222, 'h' => 112, 'align' => 'start'],
                ['x1' => 1732, 'x2' => 1908, 'y' => 222, 'h' => 112, 'align' => 'end'],
                ['x1' => 150, 'x2' => 326, 'y' => 352, 'h' => 112, 'align' => 'start'],
                ['x1' => 336, 'x2' => 512, 'y' => 352, 'h' => 112, 'align' => 'end'],
                ['x1' => 1546, 'x2' => 1722, 'y' => 352, 'h' => 112, 'align' => 'start'],
                ['x1' => 1732, 'x2' => 1908, 'y' => 352, 'h' => 112, 'align' => 'end'],
                ['x1' => 150, 'x2' => 326, 'y' => 484, 'h' => 112, 'align' => 'start'],
                ['x1' => 336, 'x2' => 512, 'y' => 484, 'h' => 112, 'align' => 'end'],
                ['x1' => 1546, 'x2' => 1722, 'y' => 484, 'h' => 112, 'align' => 'start'],
                ['x1' => 1732, 'x2' => 1908, 'y' => 484, 'h' => 112, 'align' => 'end'],
                ['x1' => 150, 'x2' => 326, 'y' => 614, 'h' => 112, 'align' => 'start'],
                ['x1' => 336, 'x2' => 512, 'y' => 614, 'h' => 112, 'align' => 'end'],
                ['x1' => 1546, 'x2' => 1722, 'y' => 614, 'h' => 112, 'align' => 'start'],
                ['x1' => 1732, 'x2' => 1908, 'y' => 614, 'h' => 112, 'align' => 'end'],
                ['x1' => 150, 'x2' => 326, 'y' => 728, 'h' => 104, 'align' => 'start'],
                ['x1' => 336, 'x2' => 512, 'y' => 728, 'h' => 104, 'align' => 'end'],
                ['x1' => 1546, 'x2' => 1722, 'y' => 728, 'h' => 104, 'align' => 'start'],
                ['x1' => 1732, 'x2' => 1908, 'y' => 728, 'h' => 104, 'align' => 'end'],
            ],
        ],
    ];

    public static function has(string $key): bool
    {
        return array_key_exists($key, self::SCENES);
    }

    public static function slotCount(string $key): int
    {
        return count(self::SCENES[$key]['slots']);
    }

    /** Percent positions of everything, ready for the page. */
    public static function layout(string $key): array
    {
        $scene = self::SCENES[$key];
        $w = $scene['width'];
        $h = $scene['height'];

        $shelves = [];
        foreach ($scene['slots'] as $slot => $s) {
            $shelves[$slot] = [
                'left' => round($s['x1'] / $w * 100, 3),
                'width' => round(($s['x2'] - $s['x1']) / $w * 100, 3),
                'bottom' => round(($h - $s['y']) / $h * 100, 3),
                'h' => $s['h'],
                'align' => $s['align'],
            ];
        }

        $window = null;
        if (isset($scene['window'])) {
            $g = $scene['window'];
            $window = [
                'left' => round($g['x1'] / $w * 100, 3),
                'top' => round($g['y1'] / $h * 100, 3),
                'width' => round(($g['x2'] - $g['x1']) / $w * 100, 3),
                'height' => round(($g['y2'] - $g['y1']) / $h * 100, 3),
            ];
        }

        return $scene + ['shelves' => $shelves, 'glass' => $window];
    }
}
