<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientTicket extends Model
{
    use HasFactory;

    protected $table = 'clientTickets';
    protected $primaryKey = 'id';
    protected $fillable = ['userID', 'supportID', 'appID', 'title', 'status', 'link', 'created_at', 'updated_at'];

    public function clientChats()
    {
        return $this->hasMany(ClientChat::class, 'ticketID', 'id');
    }

    public function user()
    {
        return $this->belongsTo(Client::class, 'userID');
    }

    public function support()
    {
        return $this->belongsTo(User::class, 'supportID');
    }

    public function application()
    {
        return $this->belongsTo(Application::class, 'appID');
    }
} 