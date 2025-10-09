<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DevChat extends Model
{
    use HasFactory;

    protected $table = 'devChats';
    protected $primaryKey = 'id';
    protected $fillable = ['ticket_id', 'sender', 'response', 'check', 'created_at', 'updated_at'];

    public function attachments()
    {
        return $this->hasMany(DevAttachment::class, 'chatID', 'id');
    }

    public function devTicket()
    {
        return $this->belongsTo(DevTicket::class, 'ticket_id');
    }
}
