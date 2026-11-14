<?php

namespace App\Http\Controllers;

use App\Models\Comment;
use App\Models\Post;
use App\Models\Review;
use App\Notifications\BookwormNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

/** Likes and comments on posts and reviews. */
class ReactionController extends Controller
{
    public function like(Request $request, string $type, int $id): JsonResponse|RedirectResponse
    {
        $item = $this->resolve($type, $id);
        $user = $request->user();

        $existing = $item->likes()->where('user_id', $user->id)->first();
        if ($existing) {
            $existing->delete();
        } else {
            $item->likes()->create(['user_id' => $user->id]);
            BookwormNotification::send($item->user, 'like', $user->name . ' liked your ' . $this->noun($item) . '.', $this->url($item), $user);
        }

        $count = $item->likes()->count();

        if ($request->expectsJson()) {
            return response()->json(['liked' => ! $existing, 'count' => $count]);
        }

        return back();
    }

    public function comment(Request $request, string $type, int $id): RedirectResponse
    {
        $item = $this->resolve($type, $id);
        $validated = $request->validate(['body' => ['required', 'string', 'max:1000']]);
        $user = $request->user();

        $comment = $item->comments()->create(['user_id' => $user->id, 'body' => trim($validated['body'])]);
        BookwormNotification::send($item->user, 'comment', $user->name . ' replied to your ' . $this->noun($item) . ': “' . str($comment->body)->limit(60) . '”', $this->url($item), $user);

        return redirect()->to(url()->previous() . '#' . $type . '-' . $id)->with('status', 'Reply posted.');
    }

    public function destroyComment(Request $request, Comment $comment): RedirectResponse
    {
        abort_unless($comment->user_id === $request->user()->id, 403);
        $comment->delete();

        return back()->with('status', 'Reply deleted.');
    }

    private function resolve(string $type, int $id): Post|Review
    {
        $class = Relation::getMorphedModel($type);
        abort_unless(in_array($class, [Post::class, Review::class], true), 404);

        return $class::with('user')->findOrFail($id);
    }

    private function noun(Model $item): string
    {
        return $item instanceof Review ? 'review' : ($item->type === 'quote' ? 'quote' : 'post');
    }

    private function url(Model $item): string
    {
        return route('feed.index') . '#' . $item->getMorphClass() . '-' . $item->getKey();
    }
}
