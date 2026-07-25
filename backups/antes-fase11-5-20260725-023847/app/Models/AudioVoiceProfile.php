<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\HasMany;
class AudioVoiceProfile extends Model{protected $fillable=['name','provider','voice','model','format','cost_per_million_chars','is_active'];protected function casts():array{return['cost_per_million_chars'=>'float','is_active'=>'boolean'];}public function narrations():HasMany{return $this->hasMany(NewsNarration::class);}}
