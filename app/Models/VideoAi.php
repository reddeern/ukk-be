<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class VideoAi extends Model
{
    protected $table = 'video_ai';
    protected $primaryKey = 'video_id';
    public $timestamps = false;
    protected $fillable = ['topeng_id', 'video_judul', 'video_file', 'video_status'];
    protected $appends = ['video_url'];
    protected $casts = ['video_created_at' => 'datetime'];

    protected function videoUrl(): Attribute
    {
        return Attribute::get(fn () => $this->video_file ? Storage::disk(config('video.disk'))->url($this->video_file) : null);
    }

    public function topeng() { return $this->belongsTo(Topeng::class, 'topeng_id'); }
}
