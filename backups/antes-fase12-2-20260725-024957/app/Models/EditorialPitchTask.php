<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\BelongsTo;
class EditorialPitchTask extends Model{protected $fillable=['editorial_pitch_id','description','is_completed','position'];protected function casts():array{return['is_completed'=>'boolean'];}public function pitch():BelongsTo{return $this->belongsTo(EditorialPitch::class,'editorial_pitch_id');}}
