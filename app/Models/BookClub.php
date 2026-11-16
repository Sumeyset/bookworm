<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BookClub extends Model
{
    protected $fillable = ['owner_id', 'name', 'emoji', 'description', 'is_private'];

    protected function casts(): array
    {
        return ['is_private' => 'boolean'];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /** Everyone invited or joined (joined_at set once they're in). */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'book_club_members')->withPivot(['role', 'joined_at'])->withTimestamps();
    }

    public function joinedMembers(): BelongsToMany
    {
        return $this->members()->whereNotNull('book_club_members.joined_at');
    }

    public function rounds(): HasMany
    {
        return $this->hasMany(BookClubRound::class)->latest('starts_on')->latest('id');
    }

    public function currentRound(): HasOne
    {
        return $this->hasOne(BookClubRound::class)->whereNull('finished_at')->latestOfMany();
    }

    public function hasMember(User $user, bool $joinedOnly = true): bool
    {
        return $this->members()
            ->where('users.id', $user->id)
            ->when($joinedOnly, fn ($q) => $q->whereNotNull('book_club_members.joined_at'))
            ->exists();
    }

    public function isOwner(User $user): bool
    {
        return $this->owner_id === $user->id;
    }

    /** Clubs a reader can see: public ones, and private ones they belong to or were invited to. */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        return $query->where(fn ($q) => $q->where('is_private', false)
            ->orWhereHas('members', fn ($m) => $m->where('users.id', $user->id)));
    }
}
