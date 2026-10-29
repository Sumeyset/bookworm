<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    public function shelfItems()
    {
        return $this->hasMany(UserBookShelf::class);
    }

    public function bookPdfs(): HasMany
    {
        return $this->hasMany(BookPdf::class);
    }

}
