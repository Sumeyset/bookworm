<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Report extends Model
{
    public const REASONS = [
        'spam' => 'Spam or ads',
        'harassment' => 'Bullying or harassment',
        'hate' => 'Hate or abuse',
        'inappropriate' => 'Inappropriate or adult content',
        'spoilers' => 'Unmarked spoilers',
        'impersonation' => 'Pretending to be someone else',
        'other' => 'Something else',
    ];

    protected $fillable = ['reporter_id', 'reported_user_id', 'reportable_type', 'reportable_id', 'reason', 'details', 'excerpt', 'status', 'resolved_by', 'resolved_at'];

    protected function casts(): array
    {
        return ['resolved_at' => 'datetime'];
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reportedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reported_user_id');
    }
}
