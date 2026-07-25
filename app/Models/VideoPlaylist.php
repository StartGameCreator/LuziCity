<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\BelongsToMany;
class VideoPlaylist extends Model{protected $fillable=['title','slug','description','is_published'];protected function casts():array{return['is_published'=>'boolean'];}public function videos():BelongsToMany{return $this->belongsToMany(Video::class,'video_playlist_items')->withPivot('position')->orderBy('video_playlist_items.position');}}
