<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DetailPesanan extends Model
{
    protected $table = 'detail_pesanan';
    protected $primaryKey = 'detail_pesanan_id';
    public $timestamps = false;
    protected $fillable = ['pesanan_id', 'produk_id', 'detail_jumlah', 'detail_harga', 'detail_subtotal'];
    protected $casts = ['detail_harga' => 'decimal:2', 'detail_subtotal' => 'decimal:2'];

    public function pesanan() { return $this->belongsTo(Pesanan::class, 'pesanan_id'); }
    public function produk() { return $this->belongsTo(Produk::class, 'produk_id'); }
}
