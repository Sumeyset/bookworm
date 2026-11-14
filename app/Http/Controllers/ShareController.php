<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserBookShelf;
use App\Support\BookTree;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Public link to a reader's tree, for sharing outside Bookworm. */
class ShareController extends Controller
{
    public function tree(Request $request, User $user): View
    {
        abort_unless($user->is_public || $request->user()?->is($user), 404);

        $items = $user->shelfItems()
            ->with(['book.reviews' => fn ($reviews) => $reviews->where('user_id', $user->id)])
            ->get();

        return view('share.tree', [
            'profile' => $user,
            'treeShelves' => BookTree::build($user, $items, false),
            'treeBookCount' => $items->count(),
            'isOwnProfile' => false,
            'shelfStatuses' => UserBookShelf::STATUSES,
            'readCount' => $items->where('status', 'read')->count(),
        ]);
    }

    public function toggle(Request $request): RedirectResponse
    {
        $user = $request->user();
        $user->forceFill(['is_public' => ! $user->is_public])->save();

        return back()->with('status', $user->is_public
            ? 'Your tree is now shareable — copy the link and send it anywhere.'
            : 'Sharing is off. Your public link no longer works.');
    }
}
