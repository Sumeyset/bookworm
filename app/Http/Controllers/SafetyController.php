<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Notifications\BookwormNotification;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

/** Blocking, follow requests and privacy settings. */
class SafetyController extends Controller
{
    public function block(Request $request, User $user): RedirectResponse
    {
        $me = $request->user();
        abort_if($me->is($user), 422);

        DB::transaction(function () use ($me, $user) {
            $me->blockedUsers()->syncWithoutDetaching([$user->id]);
            // A block ends following and follow requests both ways.
            $me->following()->detach($user->id);
            $user->following()->detach($me->id);
            $me->sentFollowRequests()->detach($user->id);
            $user->sentFollowRequests()->detach($me->id);
        });

        return to_route('feed.index')->with('status', '@' . $user->username . ' is blocked. They can’t see your profile, follow you or message you.');
    }

    public function unblock(Request $request, User $user): RedirectResponse
    {
        $request->user()->blockedUsers()->detach($user->id);

        return back()->with('status', '@' . $user->username . ' is unblocked.');
    }

    public function requests(Request $request): View
    {
        return view('users.follow-requests', [
            'requests' => $request->user()->followRequests()->orderByDesc('follow_requests.created_at')->get(),
        ]);
    }

    public function approve(Request $request, User $user): RedirectResponse
    {
        $me = $request->user();
        abort_unless($me->followRequests()->where('users.id', $user->id)->exists(), 404);
        $me->followRequests()->detach($user->id);
        $user->following()->syncWithoutDetaching([$me->id]);
        BookwormNotification::send($user, 'follow', $me->name . ' accepted your follow request.', route('users.show', $me->username), $me);

        return back()->with('status', '@' . $user->username . ' can now see your reading life.');
    }

    public function decline(Request $request, User $user): RedirectResponse
    {
        $request->user()->followRequests()->detach($user->id);

        return back()->with('status', 'Request declined.');
    }

    public function privacy(Request $request): RedirectResponse
    {
        $me = $request->user();
        $wasPrivate = $me->is_private;
        $me->forceFill([
            'is_private' => $request->boolean('is_private'),
            'hide_activity' => $request->boolean('hide_activity'),
            // A private account doesn't keep a public link.
            'is_public' => $request->boolean('is_private') ? false : (bool) $me->is_public,
        ])->save();

        // Going public again: everyone waiting gets in.
        if ($wasPrivate && ! $me->is_private) {
            $me->followRequests()->get()->each(fn (User $requester) => $requester->following()->syncWithoutDetaching([$me->id]));
            $me->followRequests()->detach();
        }

        return back()->with('status', 'privacy-updated');
    }
}
