<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class DevRole extends Model
{
    use HasFactory;

    protected $table = 'devRoles';
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'roleName',
        'created_at',
        'updated_at'
    ];
}
