<?php

namespace App\Models\Concerns;

use App\Models\Comment;
use App\Models\Like;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/** Likes and comments, shared by posts and reviews. */
trait HasReactions
{
    public function likes(): MorphMany
    {
        return $this->morphMany(Like::class, 'likeable');
    }

    public function comments(): MorphMany
    {
        return $this->morphMany(Comment::class, 'commentable')->oldest();
    }

    /** Eager-load what a feed card needs: counts, whether $viewer liked it, and the comments. */
    public function scopeWithReactions(Builder $query, User $viewer): Builder
    {
        return $query
            ->withCount(['likes', 'comments'])
            ->withExists(['likes as liked_by_viewer' => fn ($likes) => $likes->where('user_id', $viewer->id)])
            ->with(['comments.user']);
    }

    public function reactionKey(): string
    {
        return $this->getMorphClass() . ':' . $this->getKey();
    }
}
