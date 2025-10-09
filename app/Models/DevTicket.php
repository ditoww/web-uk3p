<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DevTicket extends Model
{
    use HasFactory;

    protected $table = 'devTickets';
    protected $primaryKey = 'id';
    protected $fillable = ['supportID', 'devID', 'roleID', 'title', 'status', 'link', 'priority', 'created_at', 'updated_at'];

    public function devChats()
    {
        return $this->hasMany(DevChat::class, 'ticket_id', 'id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'supportID');
    }

    public function developer()
    {
        return $this->belongsTo(User::class, 'devID');
    }

    public function role()
    {
        return $this->belongsTo(DevRole::class, 'roleID');
    }
}
