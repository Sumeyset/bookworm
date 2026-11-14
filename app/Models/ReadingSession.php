<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReadingSession extends Model
{
    protected $fillable = ['user_id', 'book_id', 'read_on', 'pages_read'];

    protected function casts(): array
    {
        return ['read_on' => 'date'];
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /** Record reading for today. Pages only ever add up. */
    public static function log(int $userId, ?int $bookId, int $pages = 0): void
    {
        $session = static::query()
            ->where('user_id', $userId)
            ->where('book_id', $bookId)
            ->whereDate('read_on', today())
            ->first()
            ?? new static(['user_id' => $userId, 'book_id' => $bookId, 'read_on' => today()]);
        $session->pages_read = (int) $session->pages_read + max(0, $pages);
        $session->save();
    }
}
