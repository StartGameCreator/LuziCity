<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\HasMany;use Illuminate\Support\Str;
class AudioSpot extends Model{protected $fillable=['name','advertiser','audio_path','audio_mime','duration_seconds','is_active'];protected function casts():array{return['is_active'=>'boolean'];}public function campaigns():HasMany{return $this->hasMany(AudioCampaign::class);}public function audioUrl():string{return Str::startsWith($this->audio_path,['http://','https://'])?$this->audio_path:asset('storage/'.$this->audio_path);}}
