<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\HasMany;
class VideoCategory extends Model{protected $fillable=['name','slug'];public function videos():HasMany{return $this->hasMany(Video::class);}}
