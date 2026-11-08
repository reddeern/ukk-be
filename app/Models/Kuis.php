<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kuis extends Model
{
    protected $table = 'kuis';
    protected $primaryKey = 'kuis_id';
    public $timestamps = false;
    protected $fillable = ['pertanyaan_teks'];

    public function pilihan() { return $this->hasMany(PilihanJawaban::class, 'kuis_id'); }
}
