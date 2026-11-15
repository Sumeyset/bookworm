<?php

namespace App\Http\Controllers;

use App\Support\ShelfStyle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

/** Saves which shelf style (tree theme or bookcase) the reader picked. */
class ShelfStyleController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $validated = $request->validate(['style' => ['required', 'string', Rule::in(ShelfStyle::keys())]]);
        $request->user()->forceFill(['shelf_style' => $validated['style']])->save();

        return redirect()->to(route('users.show', $request->user()->username) . '#bookshelf')
            ->with('status', 'Your shelf is now “' . ShelfStyle::STYLES[$validated['style']][0] . '”.');
    }
}
