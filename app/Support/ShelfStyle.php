<?php

namespace App\Support;

use App\Models\User;
use Carbon\CarbonInterface;

/**
 * Shelf styles a reader can pick for their profile.
 *
 * Tree styles decorate the tree picture; bookcase styles draw a different
 * piece of furniture that holds the same shelves.
 */
class ShelfStyle
{
    /** key => [name, description, layout, theme] */
    public const STYLES = [
        'tree' => ['Living tree', 'The classic tree in afternoon sun.', 'tree', 'plain'],
        'seasons' => ['Follow the seasons', 'Changes by itself: festive in December, golden in autumn.', 'tree', 'auto'],
        'christmas' => ['Christmas tree', 'Snowy branches, fairy lights, baubles and a star.', 'tree', 'christmas'],
        'autumn' => ['Autumn', 'Golden leaves drifting down, pumpkins at the roots.', 'tree', 'autumn'],
        'night' => ['Starry night', 'Moonlight, fireflies and a lantern on every branch.', 'tree', 'night'],
        'booktree' => ['Book Christmas tree', 'Your books stacked into a tree, with lights and a big red bow.', 'booktree', 'booktree'],
        'nook' => ['Reading nook', 'White built-in shelves around a window seat, with a ladder.', 'bookcase', 'nook'],
        'styled' => ['Styled shelf', 'Books mixed with plants, candles, a letter lamp and string lights.', 'bookcase', 'styled'],
        'library' => ['Classic library', 'A carved mahogany bookcase with brass nameplates.', 'bookcase', 'library'],
        'mantel' => ['Christmas mantel', 'Festive shelves over a crackling fireplace, with stockings.', 'bookcase', 'mantel'],
    ];

    /** Groups in the picker. */
    public const GROUPS = [
        'Bookshelves' => ['nook', 'styled', 'library'],
        'Festive' => ['booktree', 'mantel', 'christmas'],
        'Living tree' => ['tree', 'seasons', 'autumn', 'night'],
    ];

    /**
     * Styles that will need Bookworm Pro later. Empty for now: every style is free.
     * Add keys here (and check ShelfStyle::isLocked) when Pro arrives.
     */
    public const PRO = [];

    public static function keys(): array
    {
        return array_keys(self::STYLES);
    }

    /** @return array{key: string, layout: string, theme: string, name: string} */
    public static function for(User $user, ?CarbonInterface $date = null): array
    {
        $key = array_key_exists((string) $user->shelf_style, self::STYLES) ? $user->shelf_style : 'tree';
        [$name, , $layout, $theme] = self::STYLES[$key];

        if ($theme === 'auto') {
            $theme = self::season($date ?? now());
        }

        return ['key' => $key, 'layout' => $layout, 'theme' => $theme, 'name' => $name];
    }

    /** The tree theme for a date when following the seasons. */
    public static function season(CarbonInterface $date): string
    {
        $monthDay = (int) $date->format('md');

        return match (true) {
            $monthDay >= 1201 || $monthDay <= 106 => 'christmas',
            $monthDay >= 922 && $monthDay <= 1130 => 'autumn',
            default => 'plain',
        };
    }
}
