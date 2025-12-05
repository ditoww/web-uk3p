<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetailChat extends Model
{
    use HasFactory;

    protected $table = 'tb_detailchat';
    protected $primaryKey = 'idDetailChat';

    protected $fillable = [
        'deskripsi',
        'link',
        'check',
        'tanggalDetailChat',
        'type',
        'username',
        'id',
    ];
}
