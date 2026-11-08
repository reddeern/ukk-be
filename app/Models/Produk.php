<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Produk extends Model
{
    protected $table = 'produk';
    protected $primaryKey = 'produk_id';
    public $timestamps = false;
    protected $fillable = ['produk_nama', 'produk_harga', 'produk_stok', 'produk_gambar'];
    protected $casts = ['produk_harga' => 'decimal:2', 'produk_created_at' => 'datetime'];
    protected $appends = ['produk_gambar_url'];

    protected function produkGambarUrl(): Attribute
    {
        return Attribute::get(fn () => $this->produk_gambar ? asset('storage/' . $this->produk_gambar) : null);
    }
}
