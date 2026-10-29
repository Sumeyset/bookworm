<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UserBookShelf extends Model
{
    public const STATUSES = [
        'want_to_read' => 'Want to read',
        'reading' => 'Currently reading',
        'read' => 'Read',
    ];

    protected $fillable = [
        'user_id',
        'book_id',
        'status',
        'current_page',
        'total_pages',
        'read_at',
    ];

    protected function casts(): array
    {
        return ['read_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function markAsRead(): void
    {
        $readAt = $this->status === 'read' ? ($this->read_at ?? now()) : now();

        $this->forceFill([
            'status' => 'read',
            'read_at' => $readAt,
        ])->save();
    }
}
