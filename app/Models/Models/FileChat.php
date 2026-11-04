<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FileChat extends Model
{
    use HasFactory;

    protected $table = 'tb_filechat';
    protected $primaryKey = 'id';

    protected $fillable = [
        'ekstensi',
        'nameFile',
        'idDetailChat',
    ];
}
