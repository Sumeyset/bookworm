<?php

namespace App\Support;

use App\Models\User;
use Carbon\CarbonInterface;

/**
 * Shelf styles a reader can pick for their profile: the living tree (dressed
 * up with decorations from the Acorn shop) or one of the photo bookshelves.
 */
class ShelfStyle
{
    /** key => [name, description] */
    public const STYLES = [
        'tree' => ['Living tree', 'Your tree, dressed with the decorations you collect in the Acorn shop.'],
        'christmas' => ['Christmas tree shelf', 'A tree-shaped bookshelf by a crackling fireplace.'],
        'wall' => ['Wooden wall shelves', 'Warm wooden shelves with your covers on show.'],
        'window' => ['Window nook', 'Built-in shelves around a window seat. The view changes with the time of day and the season.'],
    ];

    /**
     * Styles that will need Bookworm Pro later. Empty for now: every style is free.
     */
    public const PRO = [];

    public static function keys(): array
    {
        return array_keys(self::STYLES);
    }

    /** @return array{key: string, name: string, scene: string|null} */
    public static function for(User $user): array
    {
        $key = array_key_exists((string) $user->shelf_style, self::STYLES) ? $user->shelf_style : 'tree';

        return ['key' => $key, 'name' => self::STYLES[$key][0], 'scene' => ShelfScene::has($key) ? $key : null];
    }

    /** The season (northern hemisphere) for a date: spring | summer | autumn | winter. */
    public static function season(CarbonInterface $date): string
    {
        return match ((int) $date->format('n')) {
            3, 4, 5 => 'spring',
            6, 7, 8 => 'summer',
            9, 10, 11 => 'autumn',
            default => 'winter',
        };
    }
}
