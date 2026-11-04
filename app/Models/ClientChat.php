<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientChat extends Model
{
    use HasFactory;

    protected $table = 'clientChats';
    protected $primaryKey = 'id';
    protected $fillable = ['ticketID', 'type', 'response', 'check', 'created_at', 'updated_at'];

    public function attachments()
    {
        return $this->hasMany(ClientAttachment::class, 'chatID', 'id');
    }

    public function clientTicket()
    {
        return $this->belongsTo(ClientTicket::class, 'ticketID');
    }
} 