<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username',
        'bio',
        'is_public',
        'preferred_genres',
        'yearly_reading_goal',
        'onboarding_completed_at',
        'tree_shelf_names',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'preferred_genres' => 'array',
            'tree_shelf_names' => 'array',
            'onboarding_completed_at' => 'datetime',
            'is_public' => 'boolean',
        ];
    }

    public function getTreeShelfName(int $slot, ?string $default = null): string
    {
        $names = $this->tree_shelf_names ?? [];
        if (!empty($names[$slot])) {
            return $names[$slot];
        }

        $defaultNames = [
            0 => 'Rooted Favorites',
            1 => 'Currently Reading',
            2 => 'Up Next',
            3 => 'Cozy Mysteries',
            4 => 'Big Ideas',
            5 => 'Poetry & Bedside',
            6 => 'Classics Corner',
            7 => 'Sci-Fi & Futures',
            8 => 'Heart & Hearth',
            9 => 'Ancient Wisdom',
            10 => 'Starry Night Tales',
            11 => 'Canopy Crown',
        ];

        return $default ?? ($defaultNames[$slot] ?? ('Shelf ' . ($slot + 1)));
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function bookPdfs(): HasMany
    {
        return $this->hasMany(BookPdf::class);
    }

    public function shelfItems(): HasMany
    {
        return $this->hasMany(UserBookShelf::class);
    }

    public function posts(): HasMany
    {
        return $this->hasMany(Post::class);
    }

    public function readingSessions(): HasMany
    {
        return $this->hasMany(ReadingSession::class);
    }

    public function buddyReads(): BelongsToMany
    {
        return $this->belongsToMany(BuddyRead::class)->withPivot('joined_at')->withTimestamps();
    }

    /** Days in a row (ending today or yesterday) with any reading logged. */
    public function readingStreak(): int
    {
        $days = $this->readingSessions()
            ->where('read_on', '>=', now()->subDays(400)->toDateString())
            ->distinct()
            ->orderByDesc('read_on')
            ->pluck('read_on')
            ->map(fn ($day) => \Illuminate\Support\Carbon::parse($day)->toDateString())
            ->unique()
            ->values();

        $expected = now()->startOfDay();
        if ($days->first() !== $expected->toDateString()) {
            $expected = $expected->subDay(); // a streak survives until the end of today
        }

        $streak = 0;
        foreach ($days as $day) {
            if ($day !== $expected->toDateString()) {
                break;
            }
            $streak++;
            $expected = $expected->subDay();
        }

        return $streak;
    }

    public function quoteCards(): HasMany
    {
        return $this->hasMany(QuoteCard::class);
    }

    /**
     * Reader search, Instagram style: best matches first.
     * Exact username, then usernames starting with the text, then names starting
     * with it, then a word in the name, then anything containing it. Readers the
     * viewer follows and well-followed readers come first within each group.
     */
    public function scopeSearchReaders(Builder $query, string $term, User $viewer): Builder
    {
        $term = mb_strtolower(ltrim(trim($term), '@'));
        // "!" escapes % and _ so they're searched as plain characters (works in MySQL and SQLite).
        $like = str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term);

        return $query
            ->whereKeyNot($viewer->id)
            ->where(fn ($users) => $users
                ->whereRaw("LOWER(username) LIKE ? ESCAPE '!'", ['%' . $like . '%'])
                ->orWhereRaw("LOWER(name) LIKE ? ESCAPE '!'", ['%' . $like . '%']))
            ->withCount('followers')
            ->withExists(['followers as followed_by_viewer' => fn ($followers) => $followers->where('user_follows.follower_id', $viewer->id)])
            ->orderByRaw(
                "CASE WHEN LOWER(username) = ? THEN 0 WHEN LOWER(username) LIKE ? ESCAPE '!' THEN 1 WHEN LOWER(name) LIKE ? ESCAPE '!' THEN 2 WHEN LOWER(name) LIKE ? ESCAPE '!' THEN 3 ELSE 4 END",
                [$term, $like . '%', $like . '%', '% ' . $like . '%'],
            )
            ->orderByDesc('followed_by_viewer')
            ->orderByDesc('followers_count')
            ->orderBy('name');
    }

    public function conversations(): BelongsToMany
    {
        return $this->belongsToMany(Conversation::class)->withPivot(['last_read_at', 'accepted_at'])->withTimestamps();
    }

    /** How many conversations have messages this reader hasn't seen (accepted chats only). */
    public function unreadConversationCount(): int
    {
        return $this->conversations()
            ->whereNotNull('conversation_user.accepted_at')
            ->whereHas('messages', fn ($messages) => $messages
                ->where('messages.user_id', '!=', $this->id)
                ->where(fn ($unread) => $unread
                    ->whereNull('conversation_user.last_read_at')
                    ->orWhereColumn('messages.created_at', '>', 'conversation_user.last_read_at')))
            ->count();
    }

    public function messageRequestCount(): int
    {
        return $this->conversations()->whereNull('conversation_user.accepted_at')->whereHas('messages')->count();
    }

    public function followers(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_follows',
            'followed_id',
            'follower_id',
        )->withTimestamps();
    }

    public function following(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'user_follows',
            'follower_id',
            'followed_id',
        )->withTimestamps();
    }

    public function getProfilePhotoUrlAttribute(): ?string
    {
        return $this->profile_photo_path
            ? asset('storage/' . $this->profile_photo_path)
            : null;
    }
}
