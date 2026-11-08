<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Keranjang extends Model
{
    protected $table = 'keranjang';
    protected $primaryKey = 'keranjang_id';
    public $timestamps = false;
    protected $fillable = ['pelanggan_id'];

    public function pelanggan() { return $this->belongsTo(Pelanggan::class, 'pelanggan_id'); }
    public function items() { return $this->hasMany(DetailKeranjang::class, 'keranjang_id'); }
}
