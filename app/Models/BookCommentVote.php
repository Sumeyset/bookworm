<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookCommentVote extends Model
{
    protected $fillable = ['book_comment_id', 'user_id', 'value'];

    protected function casts(): array
    {
        return ['value' => 'integer'];
    }

    public function comment()
    {
        return $this->belongsTo(BookComment::class, 'book_comment_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
