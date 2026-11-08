<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailKeranjang extends Model
{
    protected $table = 'detail_keranjang';
    protected $primaryKey = 'detail_keranjang_id';
    public $timestamps = false;
    protected $fillable = ['keranjang_id', 'produk_id', 'jumlah'];

    public function keranjang() { return $this->belongsTo(Keranjang::class, 'keranjang_id'); }
    public function produk() { return $this->belongsTo(Produk::class, 'produk_id'); }
}
