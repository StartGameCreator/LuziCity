<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\HasMany;
class RadioHost extends Model{protected $fillable=['name','bio','photo_path','is_active'];protected function casts():array{return['is_active'=>'boolean'];}public function programs():HasMany{return $this->hasMany(RadioProgram::class);}}
