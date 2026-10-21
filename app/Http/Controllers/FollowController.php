<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function store(Request $request, User $user)
    {
        if ($request->user()->id === $user->id) {
            return back()->with('status', "You can't follow yourself.");
        }

        $request->user()->following()->syncWithoutDetaching([$user->id]);

        return back()->with('status', 'You are now following @' . $user->username . '.');
    }

    public function destroy(Request $request, User $user)
    {
        $request->user()->following()->detach($user->id);

        return back()->with('status', 'You unfollowed @' . $user->username . '.');
    }
}
