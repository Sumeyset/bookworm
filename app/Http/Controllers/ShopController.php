<?php

namespace App\Http\Controllers;

use App\Support\Acorns;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** The Tree shop: spend acorns on decorations for your tree, and switch them on or off. */
class ShopController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('shop.index', [
            'balance' => (int) $user->acorns,
            'owned' => $user->decorations()->pluck('enabled', 'item'),
            'history' => $user->acornEvents()->latest('id')->take(12)->get(),
            'streak' => Acorns::streak($user),
        ]);
    }

    public function freeze(Request $request): RedirectResponse
    {
        $error = \App\Support\Streak::buyFreeze($request->user());

        return $error
            ? back()->withErrors(['shop' => $error])
            : back()->with('status', '🧊 Streak freeze ready! It will cover a day you miss.');
    }

    public function buy(Request $request, string $item): RedirectResponse
    {
        $error = Acorns::buy($request->user(), $item);
        if ($error) {
            return back()->withErrors(['shop' => $error]);
        }

        $bought = Acorns::ITEMS[$item][1] . ' ' . Acorns::ITEMS[$item][0];

        return back()->with('status', $bought . ' added to your shelves!');
    }

    public function toggle(Request $request, string $item): RedirectResponse
    {
        $decoration = $request->user()->decorations()->where('item', $item)->firstOrFail();
        $decoration->update(['enabled' => ! $decoration->enabled]);

        return back()->with('status', Acorns::ITEMS[$item][0] . ($decoration->enabled ? ' is on your tree.' : ' is put away for now.'));
    }
}
