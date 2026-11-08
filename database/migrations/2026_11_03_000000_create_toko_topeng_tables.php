<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin', function (Blueprint $t) {
            $t->id('admin_id');
            $t->string('admin_username', 50)->unique();
            $t->string('admin_password');
        });

        Schema::create('pelanggan', function (Blueprint $t) {
            $t->id('pelanggan_id');
            $t->string('nama', 100);
            $t->string('username', 50)->unique();
            $t->string('email', 150)->unique();
            $t->string('password');
        });

        Schema::create('produk', function (Blueprint $t) {
            $t->id('produk_id');
            $t->string('produk_nama', 150);
            $t->decimal('produk_harga', 12, 2);
            $t->unsignedInteger('produk_stok')->default(0);
            $t->string('produk_gambar', 255)->nullable();
            $t->timestamp('produk_created_at')->useCurrent();
        });

        Schema::create('topeng', function (Blueprint $t) {
            $t->id('topeng_id');
            $t->text('sifat_topeng');
            $t->string('topeng_nama', 150);
            $t->text('topeng_deskripsi');
            $t->string('topeng_gambar', 255)->nullable();
        });

        Schema::create('video_ai', function (Blueprint $t) {
            $t->id('video_id');
            $t->foreignId('topeng_id')->constrained('topeng', 'topeng_id')->cascadeOnDelete();
            $t->string('video_judul', 150);
            $t->string('video_file', 255)->nullable(); // path file video di storage, bukan isi video
            $t->enum('video_status', ['pending', 'processing', 'selesai', 'gagal'])->default('pending');
            $t->timestamp('video_created_at')->useCurrent();
        });

        Schema::create('kuis', function (Blueprint $t) {
            $t->id('kuis_id');
            $t->text('pertanyaan_teks');
        });

        Schema::create('pilihan_jawaban', function (Blueprint $t) {
            $t->id('pilihan_id');
            $t->foreignId('kuis_id')->constrained('kuis', 'kuis_id')->cascadeOnDelete();
            $t->foreignId('topeng_id')->constrained('topeng', 'topeng_id')->cascadeOnDelete();
            $t->text('pilihan_jawaban');
        });

        Schema::create('keranjang', function (Blueprint $t) {
            $t->id('keranjang_id');
            $t->foreignId('pelanggan_id')->unique()->constrained('pelanggan', 'pelanggan_id')->cascadeOnDelete();
        });

        Schema::create('detail_keranjang', function (Blueprint $t) {
            $t->id('detail_keranjang_id');
            $t->foreignId('keranjang_id')->constrained('keranjang', 'keranjang_id')->cascadeOnDelete();
            $t->foreignId('produk_id')->constrained('produk', 'produk_id')->cascadeOnDelete();
            $t->unsignedInteger('jumlah')->default(1);
            $t->unique(['keranjang_id', 'produk_id']);
        });

        Schema::create('pesanan', function (Blueprint $t) {
            $t->id('pesanan_id');
            $t->foreignId('pelanggan_id')->constrained('pelanggan', 'pelanggan_id')->restrictOnDelete();
            $t->timestamp('pesanan_tanggal')->useCurrent();
            $t->string('pesanan_nama_penerima', 100);
            $t->string('pesanan_no_hp', 20);
            $t->text('pesanan_alamat_pengiriman');
            $t->decimal('pesanan_subtotal', 12, 2);
            $t->decimal('pesanan_total', 12, 2);
            $t->string('pesanan_metode_pembayaran', 50);
            $t->string('pesanan_bukti_pembayaran', 255)->nullable();
            $t->enum('pesanan_status', ['menunggu', 'diproses', 'dikirim', 'selesai', 'dibatalkan'])->default('menunggu');
            $t->enum('pesanan_status_pembayaran', ['belum_bayar', 'menunggu_verifikasi', 'lunas', 'ditolak'])->default('belum_bayar');
        });

        Schema::create('detail_pesanan', function (Blueprint $t) {
            $t->id('detail_pesanan_id');
            $t->foreignId('pesanan_id')->constrained('pesanan', 'pesanan_id')->cascadeOnDelete();
            $t->foreignId('produk_id')->constrained('produk', 'produk_id')->restrictOnDelete();
            $t->unsignedInteger('detail_jumlah');
            $t->decimal('detail_harga', 12, 2);
            $t->decimal('detail_subtotal', 12, 2);
        });
    }

    public function down(): void
    {
        foreach (['detail_pesanan', 'pesanan', 'detail_keranjang', 'keranjang', 'pilihan_jawaban', 'kuis', 'video_ai', 'topeng', 'produk', 'pelanggan', 'admin'] as $t) {
            Schema::dropIfExists($t);
        }
    }
};
