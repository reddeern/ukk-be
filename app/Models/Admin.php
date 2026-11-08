<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Admin extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'admin';
    protected $primaryKey = 'admin_id';
    public $timestamps = false;
    protected $fillable = ['admin_username', 'admin_password'];
    protected $hidden = ['admin_password'];

    protected function casts(): array
    {
        return ['admin_password' => 'hashed'];
    }

    public function getAuthPassword()
    {
        return $this->admin_password;
    }
}
