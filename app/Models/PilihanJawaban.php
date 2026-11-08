<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PilihanJawaban extends Model
{
    protected $table = 'pilihan_jawaban';
    protected $primaryKey = 'pilihan_id';
    public $timestamps = false;
    protected $fillable = ['kuis_id', 'topeng_id', 'pilihan_jawaban'];

    public function kuis() { return $this->belongsTo(Kuis::class, 'kuis_id'); }
    public function topeng() { return $this->belongsTo(Topeng::class, 'topeng_id'); }
}
