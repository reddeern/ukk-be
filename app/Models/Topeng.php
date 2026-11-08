<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Topeng extends Model
{
    protected $table = 'topeng';
    protected $primaryKey = 'topeng_id';
    public $timestamps = false;
    protected $fillable = ['sifat_topeng', 'topeng_nama', 'topeng_deskripsi', 'topeng_gambar'];
    protected $appends = ['topeng_gambar_url'];

    protected function topengGambarUrl(): Attribute
    {
        return Attribute::get(fn () => $this->topeng_gambar ? asset('storage/' . $this->topeng_gambar) : null);
    }

    public function videos() { return $this->hasMany(VideoAi::class, 'topeng_id'); }
}
