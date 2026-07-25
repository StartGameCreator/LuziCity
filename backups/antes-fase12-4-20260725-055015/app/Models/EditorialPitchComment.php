<?php
namespace App\Models; use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\BelongsTo;
class EditorialPitchComment extends Model{protected $fillable=['editorial_pitch_id','user_id','body'];public function pitch():BelongsTo{return $this->belongsTo(EditorialPitch::class,'editorial_pitch_id');}public function user():BelongsTo{return $this->belongsTo(User::class);}}
