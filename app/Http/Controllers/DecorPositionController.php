<?php

namespace App\Http\Controllers;

use App\Support\ShelfStyle;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Save where a reader dragged their decorations, for one shelf style. */
class DecorPositionController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'style' => ['required', Rule::in(array_keys(ShelfStyle::STYLES))],
            'positions' => ['present', 'array', 'max:60'],
            'positions.*' => ['array', 'size:2'],
            'positions.*.*' => ['numeric', 'between:-5,105'],
        ]);

        $clean = collect($validated['positions'])
            ->filter(fn ($p, $key) => is_string($key) && preg_match('/^[a-z]+(-\d{1,2})?$/', $key))
            ->map(fn ($p) => [round((float) $p[0], 2), round((float) $p[1], 2)])
            ->all();

        $user = $request->user();
        $all = $user->decor_positions ?? [];
        $all[$validated['style']] = $clean;
        $user->forceFill(['decor_positions' => $all])->save();

        return response()->json(['saved' => count($clean)]);
    }

    public function destroy(Request $request): JsonResponse
    {
        $validated = $request->validate(['style' => ['required', Rule::in(array_keys(ShelfStyle::STYLES))]]);
        $user = $request->user();
        $all = $user->decor_positions ?? [];
        unset($all[$validated['style']]);
        $user->forceFill(['decor_positions' => $all ?: null])->save();

        return response()->json(['reset' => true]);
    }
}
