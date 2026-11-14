<?php

namespace App\Models;

use App\Models\Concerns\HasReactions;
use App\Support\FriendActivity;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    use HasReactions;

    protected $fillable = ['user_id', 'book_id', 'rating', 'body'];

    protected function casts(): array
    {
        return ['rating' => 'decimal:1'];
    }

    protected static function booted(): void
    {
        // Followers hear about ratings and reviews in their notifications (not the feed).
        static::created(function (Review $review): void {
            filled($review->body) ? FriendActivity::reviewed($review) : FriendActivity::rated($review);
        });

        // A star rating that later gets written words counts as a review.
        static::updated(function (Review $review): void {
            if ($review->wasChanged('body') && blank($review->getOriginal('body')) && filled($review->body)) {
                FriendActivity::reviewed($review);
            }
        });
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }
}
