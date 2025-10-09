<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Contracts\Auth\CanResetPassword;
use Illuminate\Auth\Passwords\CanResetPassword as CanResetPasswordTrait;

class User extends Authenticatable implements CanResetPassword
{
    use HasFactory, CanResetPasswordTrait;

    protected $table = 'users'; // tabel baru
    protected $primaryKey = 'id';
    public $incrementing = true;
    protected $keyType = 'int';

    protected $fillable = [
        'username',
        'name',
        'email',
        'phone_number',
        'privLevel',
        'profile_picture',
        'devRoleID',
        'created_at',
        'updated_at',
        'status'
    ];
    
    protected $hidden = [
        'password',
        'phone_number',
    ];

    // === Manual Query: all data dengan role (khusus admin)
    public function alldatad()
    {
        return DB::table('users')
            ->leftJoin('devRoles', 'users.devRoleID', '=', 'devRoles.id')
            ->where('users.privLevel', '=', 'developer')
            ->select('users.*', 'devRoles.roleName as nameRole')
            ->get();
    }

    // === Tanpa filter level akun
    public function alldata()
    {
        return DB::table('users')
            ->leftJoin('devRoles', 'users.devRoleID', '=', 'devRoles.id')
            ->select('users.*', 'devRoles.roleName')
            ->get();
    }

    public function addData($data)
    {
        return DB::table('users')->insert($data);
    }

    public function isDeveloper(): bool {
        return $this->privLevel == "developer";
    }

    public function isSPV(): bool {
        return $this->privLevel == "spv";
    }

    public function isSupport(): bool {
        return $this->privLevel == "support";
    }

    public function isAdmin(): bool {
        return $this->privLevel == "admin";
    }

    public function isSuperAdmin(): bool {
        return $this->privLevel == "super_admin";
    }
}
