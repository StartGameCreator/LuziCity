<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\HasMany;
class RadioStation extends Model{protected $fillable=['name','call_sign','description','stream_url','logo_path','is_active','force_on_air','on_air_label'];protected function casts():array{return['is_active'=>'boolean','force_on_air'=>'boolean'];}public function programs():HasMany{return $this->hasMany(RadioProgram::class);}}
