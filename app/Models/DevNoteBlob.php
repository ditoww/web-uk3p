<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DevNoteBlob extends Model
{
    protected $table = 'dev_note_blobs';

    protected $fillable = [
        'user_id',
        'title',
        'file_name',
        'mime_type',
        'size',
        'blob',
    ];
}