<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class MessageAttachment extends Model
{
    protected $fillable = ['message_id', 'type', 'path', 'original_name', 'mime', 'size', 'width', 'height', 'duration'];

    protected function casts(): array
    {
        return ['duration' => 'float'];
    }

    protected static function booted(): void
    {
        static::deleted(function (MessageAttachment $attachment): void {
            Storage::disk('local')->delete($attachment->path);
        });
    }

    public function message(): BelongsTo
    {
        return $this->belongsTo(Message::class);
    }

    public function url(): string
    {
        return route('messages.attachment', $this);
    }

    public function sizeLabel(): string
    {
        return $this->size >= 1048576 ? number_format($this->size / 1048576, 1) . ' MB' : max(1, (int) round($this->size / 1024)) . ' KB';
    }

    public function durationLabel(): string
    {
        $seconds = (int) round($this->duration ?? 0);

        return intdiv($seconds, 60) . ':' . str_pad((string) ($seconds % 60), 2, '0', STR_PAD_LEFT);
    }

    /** Remove the stored files for many attachments at once (rows go by cascade). */
    public static function deleteFilesFor($query): void
    {
        $query->pluck('path')->each(fn ($path) => Storage::disk('local')->delete($path));
    }
}
