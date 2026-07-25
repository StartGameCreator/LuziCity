<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\HasMany;
class AiAgent extends Model{protected $fillable=['slug','name','instructions','is_enabled','position'];protected function casts():array{return['is_enabled'=>'boolean'];}public function runs():HasMany{return $this->hasMany(AiAgentRun::class);}}
