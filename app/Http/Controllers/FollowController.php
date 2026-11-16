<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\BookwormNotification;
use Illuminate\Http\Request;

class FollowController extends Controller
{
    public function store(Request $request, User $user)
    {
        $me = $request->user();
        if ($me->id === $user->id) {
            return back()->with('status', "You can't follow yourself.");
        }
        abort_if($me->isBlockedWith($user), 404);

        if ($me->isFollowing($user)) {
            return back()->with('status', 'You already follow @' . $user->username . '.');
        }

        // Private accounts approve their followers.
        if ($user->is_private) {
            $changes = $me->sentFollowRequests()->syncWithoutDetaching([$user->id]);
            if (! empty($changes['attached'])) {
                BookwormNotification::send($user, 'follow', $me->name . ' asked to follow you.', route('follow-requests.index'), $me);
            }

            return back()->with('status', 'Follow request sent to @' . $user->username . '.');
        }

        $changes = $me->following()->syncWithoutDetaching([$user->id]);
        if (! empty($changes['attached'])) {
            BookwormNotification::send($user, 'follow', $me->name . ' started following you.', route('users.show', $me->username), $me);
        }

        return back()->with('status', 'You are now following @' . $user->username . '.');
    }

    public function destroy(Request $request, User $user)
    {
        $request->user()->following()->detach($user->id);
        $request->user()->sentFollowRequests()->detach($user->id);

        return back()->with('status', 'You unfollowed @' . $user->username . '.');
    }
}
