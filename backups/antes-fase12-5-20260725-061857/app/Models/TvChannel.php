<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\HasMany;
class TvChannel extends Model{protected $fillable=['name','slug','description','logo_path','is_active'];protected function casts():array{return['is_active'=>'boolean'];}public function getRouteKeyName():string{return 'slug';}public function broadcasts():HasMany{return $this->hasMany(TvBroadcast::class);}}
