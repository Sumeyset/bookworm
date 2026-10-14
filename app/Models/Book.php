<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'ol_id',
        'title',
        'author_name',
        'cover_url',
        'first_publish_year',
    ];

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function comments()
    {
        return $this->hasMany(BookComment::class);
    }
}
