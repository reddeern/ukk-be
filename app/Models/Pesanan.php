<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pesanan extends Model
{
    protected $table = 'pesanan';
    protected $primaryKey = 'pesanan_id';
    public $timestamps = false;
    protected $fillable = [
        'pelanggan_id', 'pesanan_nama_penerima', 'pesanan_no_hp', 'pesanan_alamat_pengiriman',
        'pesanan_subtotal', 'pesanan_total', 'pesanan_metode_pembayaran',
        'pesanan_bukti_pembayaran', 'pesanan_status', 'pesanan_status_pembayaran',
    ];
    protected $casts = [
        'pesanan_tanggal' => 'datetime',
        'pesanan_subtotal' => 'decimal:2',
        'pesanan_total' => 'decimal:2',
    ];
    protected $appends = ['pesanan_bukti_url'];

    public function getPesananBuktiUrlAttribute()
    {
        return $this->pesanan_bukti_pembayaran ? asset('storage/' . $this->pesanan_bukti_pembayaran) : null;
    }

    public function pelanggan() { return $this->belongsTo(Pelanggan::class, 'pelanggan_id'); }
    public function details() { return $this->hasMany(DetailPesanan::class, 'pesanan_id'); }

    /** Batalkan pesanan & kembalikan stok. Harus dipanggil di dalam transaksi. */
    public function batalkan(): void
    {
        if ($this->pesanan_status === 'dibatalkan') {
            return;
        }
        foreach ($this->details as $d) {
            Produk::where('produk_id', $d->produk_id)->increment('produk_stok', $d->detail_jumlah);
        }
        $this->update(['pesanan_status' => 'dibatalkan']);
    }
}
