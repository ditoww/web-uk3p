<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Content extends Model
{
    protected $fillable = [
        'section', 'title', 'subtitle', 'body', 'icon',
        'link', 'image', 'sort_order', 'is_published',
        'event_date', 'event_time', 'location',
    ];

    protected $casts = ['is_published' => 'boolean', 'event_date' => 'date'];

    public function scopeSection($q, string $section)
    {
        return $q->where('section', $section);
    }

    public function scopeTampil($q)
    {
        return $q->where('is_published', true)->orderBy('sort_order')->orderBy('id');
    }
}