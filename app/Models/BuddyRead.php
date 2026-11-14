<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class BuddyRead extends Model
{
    protected $fillable = ['book_id', 'creator_id', 'title'];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    /** Everyone invited; joined_at is set once they accept. */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot('joined_at')->withTimestamps();
    }

    public function comments(): HasMany
    {
        return $this->hasMany(BuddyReadComment::class)->oldest();
    }

    public function latestComment(): HasOne
    {
        return $this->hasOne(BuddyReadComment::class)->latestOfMany();
    }

    public function hasMember(User $user, bool $joinedOnly = true): bool
    {
        return $this->members()
            ->where('users.id', $user->id)
            ->when($joinedOnly, fn ($q) => $q->whereNotNull('buddy_read_user.joined_at'))
            ->exists();
    }
}
