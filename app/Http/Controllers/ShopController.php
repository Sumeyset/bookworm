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
            'gifts' => $user->decorations()->whereNotNull('gifted_by')->with('giver')->get()->keyBy('item'),
            'friends' => $user->following()->orderBy('name')->get(['users.id', 'users.name', 'users.username']),
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

    public function gift(Request $request, string $item): RedirectResponse
    {
        $validated = $request->validate([
            'friend_id' => ['required', 'integer', \Illuminate\Validation\Rule::exists('user_follows', 'followed_id')->where('follower_id', $request->user()->id)],
        ], ['friend_id.required' => 'Pick a friend to give it to.']);

        $friend = \App\Models\User::findOrFail($validated['friend_id']);
        $error = Acorns::gift($request->user(), $friend, $item);

        return $error
            ? back()->withErrors(['shop' => $error])
            : back()->with('status', '🎁 Sent! ' . $friend->name . ' just got the ' . Acorns::ITEMS[$item][0] . '.');
    }

    public function toggle(Request $request, string $item): RedirectResponse
    {
        $decoration = $request->user()->decorations()->where('item', $item)->firstOrFail();
        $decoration->update(['enabled' => ! $decoration->enabled]);

        return back()->with('status', Acorns::ITEMS[$item][0] . ($decoration->enabled ? ' is on your tree.' : ' is put away for now.'));
    }
}
