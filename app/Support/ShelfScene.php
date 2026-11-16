<?php

namespace App\Support;

/**
 * Photo bookshelves (public/images/shelves). Each scene lists where its
 * shelves are in the picture, in image pixels: books stand on the line y,
 * between x1 and x2. Slot 0 is the first shelf books fill.
 *
 * Scenes with 'grow' get bigger as the shelves fill: a band of the picture
 * (y1..y2, from the scene picture or its own 'image') is repeated at 'at',
 * each copy bringing the shelves listed in grow.slots (dy = from band top).
 *
 *   h      tallest book that fits there (image px)
 *   align  where books gather on the shelf: start | center | end
 *   cap    how many books fit (default UserBookShelf::MAX_BOOKS_PER_SHELF)
 *   lean   books lean this many degrees (+ = top to the right), each resting on the next
 *   room   room left at the foot of the board they lean on, as a share of h
 *   pile   books lie flat in a stack (on the floor)
 *          (0 when they lie flat against a slanted board)
 */
class ShelfScene
{
    public const SCENES = [
        // Christmas tree bookshelf by the fireplace. Books stand as spines and lean
        // in towards the trunk from both sides, so full shelves make a tree shape too.
        // The little side triangles hold one book each.
        'christmas' => [
            'image' => 'images/shelves/christmas-tree-shelf.webp',
            'width' => 1240,
            'height' => 1116,
            'view' => 'spines',
            'spine' => 23,          // spine thickness (px)
            'min_width' => 640,     // narrow screens scroll sideways from this width
            // Where Tree shop decorations go in this picture
            'decor' => [
                'star' => [622, 112],
                'ornaments' => [[566, 196], [680, 214], [508, 330], [742, 344], [446, 470], [800, 486], [384, 610], [862, 626], [322, 752], [920, 770]],
                'gifts' => [[455, 1035], [520, 1040], [790, 1032]],
                'pumpkins' => [[800, 1032], [460, 1035]],
                'leaves' => [[380, 1040, -30], [430, 1052, 40], [560, 1045, 10], [700, 1050, -60], [760, 1040, 25], [880, 1050, -15], [620, 1060, 70], [330, 1060, 80], [930, 1062, -45]],
                'lanterns' => [[200, 120], [1030, 200], [430, 140], [820, 150]],
                'nest' => [1180, 440],
                'owl' => [1146, 132],
                'swing' => [920, 862],
                'squirrel' => [1070, 400],
                'butterflies' => [[200, 520], [960, 560], [330, 200], [1000, 300]],
            ],
            'slots' => [
                // rows 1–3: books lean in on the middle board, like the tree's outline
                ['x1' => 540, 'x2' => 612, 'y' => 276, 'h' => 96, 'align' => 'end', 'cap' => 2, 'lean' => 13, 'room' => .2],
                ['x1' => 628, 'x2' => 700, 'y' => 276, 'h' => 96, 'align' => 'start', 'cap' => 2, 'lean' => -13, 'room' => .2],
                ['x1' => 500, 'x2' => 612, 'y' => 428, 'h' => 126, 'align' => 'end', 'lean' => 13, 'room' => .2],
                ['x1' => 628, 'x2' => 745, 'y' => 428, 'h' => 126, 'align' => 'start', 'lean' => -13, 'room' => .2],
                ['x1' => 446, 'x2' => 612, 'y' => 575, 'h' => 132, 'align' => 'end', 'cap' => 4, 'lean' => 13, 'room' => .2],
                ['x1' => 628, 'x2' => 798, 'y' => 575, 'h' => 132, 'align' => 'start', 'cap' => 4, 'lean' => -13, 'room' => .2],
                // rows 4–5: the middle books lie against the slanted boards; the side
                // triangles span the whole triangle so they are easy to drop on
                ['x1' => 377, 'x2' => 517, 'y' => 717, 'h' => 118, 'align' => 'center', 'cap' => 1],
                ['x1' => 534, 'x2' => 614, 'y' => 717, 'h' => 122, 'align' => 'start', 'lean' => -30],
                ['x1' => 626, 'x2' => 703, 'y' => 717, 'h' => 122, 'align' => 'end', 'lean' => 30],
                ['x1' => 722, 'x2' => 862, 'y' => 717, 'h' => 118, 'align' => 'center', 'cap' => 1],
                ['x1' => 320, 'x2' => 480, 'y' => 861, 'h' => 118, 'align' => 'center', 'cap' => 1],
                ['x1' => 497, 'x2' => 614, 'y' => 861, 'h' => 124, 'align' => 'start', 'lean' => -30],
                ['x1' => 626, 'x2' => 739, 'y' => 861, 'h' => 124, 'align' => 'end', 'lean' => 30],
                ['x1' => 782, 'x2' => 942, 'y' => 861, 'h' => 118, 'align' => 'center', 'cap' => 1],
                // the tree can't grow, so more books pile up on the floor around it
                ['x1' => 175, 'x2' => 335, 'y' => 985, 'h' => 112, 'align' => 'center', 'cap' => 5, 'pile' => true],
                ['x1' => 855, 'x2' => 1015, 'y' => 975, 'h' => 112, 'align' => 'center', 'cap' => 5, 'pile' => true],
                ['x1' => 345, 'x2' => 505, 'y' => 1100, 'h' => 116, 'align' => 'center', 'cap' => 5, 'pile' => true],
                ['x1' => 690, 'x2' => 850, 'y' => 1100, 'h' => 116, 'align' => 'center', 'cap' => 5, 'pile' => true],
                ['x1' => 20, 'x2' => 175, 'y' => 1100, 'h' => 112, 'align' => 'center', 'cap' => 5, 'pile' => true],
                ['x1' => 517, 'x2' => 677, 'y' => 1108, 'h' => 112, 'align' => 'center', 'cap' => 5, 'pile' => true],
            ],
        ],

        // Plain wooden wall shelves. Books face out, so their covers show.
        'wall' => [
            'image' => 'images/shelves/wall-shelf.webp',
            'width' => 1800,
            'height' => 1200,
            'view' => 'covers',
            'decor_scale' => 1.2,
            'decor' => [
                'star' => [900, 30],
                'ornaments' => [[330, 292], [900, 296], [1470, 292], [615, 576], [1185, 576], [330, 850], [1470, 850], [900, 852]],
                'gifts' => [[1745, 1082], [58, 1082], [615, 1082]],
                'pumpkins' => [[40, 1082], [1765, 1082]],
                'leaves' => [[200, 1110, -30], [380, 1120, 40], [560, 1112, 10], [760, 1125, -60], [960, 1110, 25], [1140, 1122, -15], [1320, 1112, 70], [1500, 1124, 80], [1680, 1114, -45]],
                'lanterns' => [[615, 300], [1185, 300], [615, 860], [1185, 860]],
                'nest' => [615, 554],
                'owl' => [1185, 827],
                'swing' => [1185, 280],
                'squirrel' => [1765, 640],
                'butterflies' => [[300, 330], [1300, 610], [900, 880], [1600, 330]],
            ],
            'book' => 146,          // cover width (px)
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
            // More books: another row of shelves (picture y 575..848) goes in
            // above the bottom row, as many times as needed.
            'grow' => [
                'at' => 848, 'y1' => 575, 'y2' => 848,
                'slots' => [
                    ['x1' => 70, 'x2' => 590, 'dy' => 252, 'h' => 230, 'align' => 'center'],
                    ['x1' => 640, 'x2' => 1160, 'dy' => 252, 'h' => 230, 'align' => 'center'],
                    ['x1' => 1210, 'x2' => 1730, 'dy' => 252, 'h' => 230, 'align' => 'center'],
                ],
            ],
        ],

        // A window seat between two tall bookcases, with cubbies under the seat.
        // The view outside changes with the time of day and the season.
        'window' => [
            'image' => 'images/shelves/window-nook.webp',
            'width' => 2000,
            'height' => 1116,
            'view' => 'spines',
            'spine' => 34,
            'min_width' => 900,
            'decor_scale' => 1.5,
            // the glass, so the view can sit behind it
            'window' => ['x1' => 645, 'y1' => 130, 'x2' => 1356, 'y2' => 872],
            // the bottom sashes (images/shelves/window-sash.webp): they slide up this far to open the window
            // curtains on a rod across the top of the recess (drawn over the picture)
            'curtains' => ['x1' => 552, 'y1' => 26, 'x2' => 1448, 'y2' => 884],
            'sash' => ['image' => 'images/shelves/window-sash.webp', 'x1' => 645, 'y1' => 530, 'x2' => 1356, 'y2' => 872, 'open_by' => 330],
            'decor' => [
                'star' => [1000, 78],
                'ornaments' => [[730, 112], [850, 116], [1150, 116], [1270, 112], [620, 300], [1380, 300]],
                'gifts' => [[650, 930], [712, 930], [1352, 930]],
                'pumpkins' => [[1345, 930], [660, 930]],
                'leaves' => [[600, 934, -30], [760, 938, 40], [840, 934, 10], [960, 938, -60], [1060, 934, 25], [1140, 938, -15], [1220, 934, 70], [1300, 938, 80], [1420, 934, -45]],
                'lanterns' => [[606, 150], [1394, 150], [300, 70], [1710, 70]],
                'nest' => [1180, 930],
                'owl' => [880, 930],
                'swing' => [1000, 112],
                'squirrel' => [560, 600],
                'butterflies' => [[1100, 320], [860, 640], [1640, 330], [360, 560]],
            ],
            'slots' => [
                // the two bookcases, shelf by shelf (left, right), top to bottom
                ['x1' => 86, 'x2' => 500, 'y' => 205, 'h' => 135, 'align' => 'start', 'cap' => 6],
                ['x1' => 1500, 'x2' => 1916, 'y' => 205, 'h' => 135, 'align' => 'end', 'cap' => 6],
                ['x1' => 86, 'x2' => 500, 'y' => 452, 'h' => 220, 'align' => 'start', 'cap' => 6],
                ['x1' => 1500, 'x2' => 1916, 'y' => 452, 'h' => 220, 'align' => 'end', 'cap' => 6],
                ['x1' => 86, 'x2' => 500, 'y' => 693, 'h' => 210, 'align' => 'start', 'cap' => 6],
                ['x1' => 1500, 'x2' => 1916, 'y' => 693, 'h' => 210, 'align' => 'end', 'cap' => 6],
                ['x1' => 86, 'x2' => 500, 'y' => 900, 'h' => 178, 'align' => 'start', 'cap' => 6],
                ['x1' => 1500, 'x2' => 1916, 'y' => 900, 'h' => 178, 'align' => 'end', 'cap' => 6],
                ['x1' => 86, 'x2' => 500, 'y' => 1098, 'h' => 158, 'align' => 'start', 'cap' => 6],
                ['x1' => 1500, 'x2' => 1916, 'y' => 1098, 'h' => 158, 'align' => 'end', 'cap' => 6],
                ['x1' => 540, 'x2' => 982, 'y' => 1098, 'h' => 116, 'align' => 'center', 'cap' => 5],
                ['x1' => 1018, 'x2' => 1460, 'y' => 1098, 'h' => 116, 'align' => 'center', 'cap' => 5],
            ],
            // More books: one more shelf row in each bookcase and another pair of
            // cubbies under the seat, added at the bottom (images/shelves/window-band.webp).
            'grow' => [
                'at' => 1116, 'image' => 'images/shelves/window-band.webp', 'y1' => 0, 'y2' => 203,
                'slots' => [
                    ['x1' => 86, 'x2' => 500, 'dy' => 185, 'h' => 150, 'align' => 'start', 'cap' => 6],
                    ['x1' => 1500, 'x2' => 1916, 'dy' => 185, 'h' => 150, 'align' => 'end', 'cap' => 6],
                    ['x1' => 540, 'x2' => 982, 'dy' => 185, 'h' => 148, 'align' => 'center', 'cap' => 5],
                    ['x1' => 1018, 'x2' => 1460, 'dy' => 185, 'h' => 148, 'align' => 'center', 'cap' => 5],
                ],
            ],
        ],
    ];

    public static function has(string $key): bool
    {
        return array_key_exists($key, self::SCENES);
    }

    /** All shelves of the scene with $bands extra bands, in picture pixels (y already shifted). */
    private static function slots(string $key, int $bands = 0): array
    {
        $scene = self::SCENES[$key];
        $grow = $scene['grow'] ?? null;
        if (! $grow || $bands <= 0) {
            return $scene['slots'];
        }
        $bh = $grow['y2'] - $grow['y1'];
        $slots = array_map(fn ($s) => $s['y'] >= $grow['at'] ? ['y' => $s['y'] + $bands * $bh] + $s : $s, $scene['slots']);
        for ($k = 0; $k < $bands; $k++) {
            foreach ($grow['slots'] as $t) {
                $slots[] = ['y' => $grow['at'] + $k * $bh + $t['dy']] + $t;
            }
        }

        return $slots;
    }

    public static function slotCount(string $key, int $bands = 0): int
    {
        return count(self::slots($key, $bands));
    }

    /** How many extra bands a scene needs to give every one of $needed shelves a place. */
    public static function bandsFor(string $key, int $needed): int
    {
        $grow = self::SCENES[$key]['grow'] ?? null;
        $base = count(self::SCENES[$key]['slots']);
        if (! $grow || $needed <= $base) {
            return 0;
        }
        $per = count($grow['slots']);
        $most = intdiv(\App\Models\UserBookShelf::MAX_SHELVES - $base, $per);

        return min($most, (int) ceil(($needed - $base) / $per));
    }

    /** @return array<int, int> slot => how many books fit, for every slot of the scene */
    public static function capacities(string $key, int $bands = 0): array
    {
        return array_map(fn ($slot) => $slot['cap'] ?? \App\Models\UserBookShelf::MAX_BOOKS_PER_SHELF, self::slots($key, $bands));
    }

    /** How many books fit on one of this reader's shelves, in their current shelf style. */
    public static function capacityFor(\App\Models\User $user, int $slot): int
    {
        $scene = ShelfStyle::for($user)['scene'];

        return $scene ? (self::capacities($scene, self::bandsFor($scene, $slot + 1))[$slot] ?? \App\Models\UserBookShelf::MAX_BOOKS_PER_SHELF) : \App\Models\UserBookShelf::MAX_BOOKS_PER_SHELF;
    }

    /**
     * Place a reader's books on a photo scene, growing it as far as needed.
     * Returns the shelves (as BookTree::build does) for exactly the scene's slots.
     */
    public static function shelves(string $key, \App\Models\User $owner, \Illuminate\Support\Collection $items, bool $isOwner): \Illuminate\Support\Collection
    {
        $capacities = self::capacities($key);
        if (! isset(self::SCENES[$key]['grow'])) {
            // A scene that can't grow has no room past its last shelf: books
            // saved further along move onto the first free shelf instead.
            $capacities += array_fill(0, \App\Models\UserBookShelf::MAX_SHELVES, 0);
        }
        $first = BookTree::build($owner, $items, $isOwner, self::slotCount($key), $capacities);
        $bands = self::bandsFor($key, $first->count());
        $count = self::slotCount($key, $bands);
        $shelves = $bands === 0 ? $first : BookTree::build($owner, $items, $isOwner, $count, self::capacities($key, $bands));

        return $shelves->filter(fn ($s) => $s['slot'] < $count)->values();
    }

    /** Percent positions of everything, ready for the page. */
    public static function layout(string $key, int $bands = 0): array
    {
        $scene = self::SCENES[$key];
        $grow = $scene['grow'] ?? null;
        $w = $scene['width'];
        $bh = ($grow && $bands > 0) ? $grow['y2'] - $grow['y1'] : 0;
        $h = $scene['height'] + $bands * $bh;
        $pct = fn ($v, $of) => round($v / $of * 100, 4);

        // The picture, in pieces: top, the repeated bands, then the rest.
        if ($bh) {
            $pieces = [['image' => $scene['image'], 'from' => 0, 'top' => 0, 'h' => $grow['at']]];
            for ($k = 0; $k < $bands; $k++) {
                $pieces[] = ['image' => $grow['image'] ?? $scene['image'], 'from' => $grow['y1'], 'top' => $grow['at'] + $k * $bh, 'h' => $bh];
            }
            if ($scene['height'] > $grow['at']) {
                $pieces[] = ['image' => $scene['image'], 'from' => $grow['at'], 'top' => $grow['at'] + $bands * $bh, 'h' => $scene['height'] - $grow['at']];
            }
            $pieces = array_map(fn ($p) => [
                'image' => $p['image'],
                'top' => $pct($p['top'], $h),
                'height' => $pct($p['h'], $h),
                'shift' => $pct($p['from'], $w),    // margin-top % is of the width
            ], $pieces);
        } else {
            $pieces = null;
        }

        $shelves = [];
        foreach (self::slots($key, $bands) as $slot => $s) {
            $shelves[$slot] = [
                'left' => $pct($s['x1'], $w),
                'width' => $pct($s['x2'] - $s['x1'], $w),
                'bottom' => $pct($h - $s['y'], $h),
                'h' => $s['h'],
                'align' => $s['align'],
                'cap' => $s['cap'] ?? \App\Models\UserBookShelf::MAX_BOOKS_PER_SHELF,
                'pile' => $s['pile'] ?? false,
                'lean' => $s['lean'] ?? 0,
                'room' => $s['room'] ?? 0,
                // leaning books sit a little further apart: thickness × (1/cos − 1)
                'lean_gap' => isset($s['lean']) ? round(($scene['spine'] ?? 20) * (1 / cos(deg2rad(abs($s['lean']))) - 1) + 1, 2) : 0,
            ];
        }

        $window = null;
        if (isset($scene['window'])) {
            $g = $scene['window'];
            $shift = $g['y1'] >= ($grow['at'] ?? PHP_INT_MAX) ? $bands * $bh : 0;
            $window = [
                'left' => $pct($g['x1'], $w),
                'top' => $pct($g['y1'] + $shift, $h),
                'width' => $pct($g['x2'] - $g['x1'], $w),
                'height' => $pct($g['y2'] - $g['y1'], $h),
            ];
        }

        // Decoration spots, as page percentages (moved down with the shelves when it grew).
        $point = function (array $p) use ($grow, $bands, $bh, $w, $h, $pct) {
            $y = $p[1] >= ($grow['at'] ?? PHP_INT_MAX) ? $p[1] + $bands * $bh : $p[1];

            return ['left' => $pct($p[0], $w), 'top' => $pct($y, $h)];
        };
        $d = $scene['decor'] ?? [];
        $decor = $d ? [
            'star' => $point($d['star']),
            'ornaments' => array_map($point, $d['ornaments']),
            'gifts' => array_map($point, $d['gifts']),
            'pumpkins' => array_map($point, $d['pumpkins']),
            'leaves' => array_map(fn ($l) => ['at' => $point($l), 'turn' => $l[2]], $d['leaves']),
            'lanterns' => array_map($point, $d['lanterns']),
            'nest' => $point($d['nest']),
            'owl' => $point($d['owl']),
            'swing' => $point($d['swing']),
            'squirrel' => $point($d['squirrel']),
            'butterflies' => array_map($point, $d['butterflies']),
            'window' => null,   // the window nook has a real window
        ] : null;

        $sash = null;
        if (isset($scene['sash'])) {
            $s = $scene['sash'];
            $sash = [
                'image' => $s['image'],
                'left' => $pct($s['x1'], $w),
                'top' => $pct($s['y1'], $h),
                'width' => $pct($s['x2'] - $s['x1'], $w),
                'height' => $pct($s['y2'] - $s['y1'], $h),
                // how far it slides, as a share of its own height
                'open' => round($s['open_by'] / ($s['y2'] - $s['y1']) * 100, 2),
            ];
        }

        $curtains = isset($scene['curtains']) ? [
            'left' => $pct($scene['curtains']['x1'], $w),
            'top' => $pct($scene['curtains']['y1'], $h),
            'width' => $pct($scene['curtains']['x2'] - $scene['curtains']['x1'], $w),
            'height' => $pct($scene['curtains']['y2'] - $scene['curtains']['y1'], $h),
        ] : null;

        return ['height' => $h, 'pieces' => $pieces, 'shelves' => $shelves, 'glass' => $window, 'decor' => $decor, 'sash' => $sash, 'curtains' => $curtains] + $scene;
    }
}
