<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Pelanggan extends Authenticatable
{
    use HasApiTokens;

    protected $table = 'pelanggan';
    protected $primaryKey = 'pelanggan_id';
    public $timestamps = false;
    protected $fillable = ['nama', 'username', 'email', 'password'];
    protected $hidden = ['password'];

    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }

    public function keranjang() { return $this->hasOne(Keranjang::class, 'pelanggan_id'); }
    public function pesanan() { return $this->hasMany(Pesanan::class, 'pelanggan_id'); }
}
