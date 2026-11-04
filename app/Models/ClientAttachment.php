<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientAttachment extends Model
{
    use HasFactory;

    protected $table = 'clientAttachments';
    protected $primaryKey = 'id';
    protected $fillable = ['chatID', 'fileName', 'fileExtension', 'created_at', 'updated_at'];

    public function clientChat()
    {
        return $this->belongsTo(ClientChat::class, 'chatID', 'id');
    }
} 