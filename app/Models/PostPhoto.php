<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PostPhoto extends Model
{
    protected $fillable = ['post_id', 'path', 'width', 'height', 'position', 'focus_x', 'focus_y', 'filter'];

    protected static function booted(): void
    {
        static::deleted(function (PostPhoto $photo): void {
            Storage::disk('public')->delete($photo->path);
        });
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }

    public function url(): string
    {
        return asset('storage/' . $this->path);
    }

    public function filterClass(): string
    {
        return in_array($this->filter, Post::PHOTO_FILTERS, true) ? 'pf-' . $this->filter : 'pf-normal';
    }
}
