<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\BelongsTo;
class EditorialSourceClaim extends Model{protected $fillable=['editorial_pitch_id','editorial_pitch_source_id','claim','status','contradiction_note'];public function pitch():BelongsTo{return $this->belongsTo(EditorialPitch::class,'editorial_pitch_id');}public function source():BelongsTo{return $this->belongsTo(EditorialPitchSource::class,'editorial_pitch_source_id');}}
