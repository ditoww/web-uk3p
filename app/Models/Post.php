<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Post extends Model
{
    protected $fillable = [
        'title', 'slug', 'body', 'cover_image',
        'is_published', 'published_at', 'author_name',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    public function getExcerptAttribute(): string
    {
        return Str::limit(strip_tags($this->body), 140);
    }
}