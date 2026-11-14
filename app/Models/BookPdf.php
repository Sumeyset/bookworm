<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class BookPdf extends Model
{
    protected $fillable = [
        'user_id',
        'book_id',
        'path',
        'original_name',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    /** File size for display, e.g. "3.2 MB" (empty if the file is missing). */
    public function sizeLabel(): string
    {
        try {
            $bytes = Storage::disk('local')->size($this->path);
        } catch (\Throwable) {
            return '';
        }

        return $bytes >= 1048576 ? number_format($bytes / 1048576, 1) . ' MB' : max(1, (int) round($bytes / 1024)) . ' KB';
    }

    public function highlights(): HasMany
    {
        return $this->hasMany(BookPdfHighlight::class);
    }
}
