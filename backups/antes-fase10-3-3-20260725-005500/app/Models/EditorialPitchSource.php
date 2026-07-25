<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\BelongsTo;
class EditorialPitchSource extends Model{protected $fillable=['editorial_pitch_id','title','url','notes'];public function pitch():BelongsTo{return $this->belongsTo(EditorialPitch::class,'editorial_pitch_id');}}
