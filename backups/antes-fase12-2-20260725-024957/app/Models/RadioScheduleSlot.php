<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\BelongsTo;
class RadioScheduleSlot extends Model{protected $fillable=['radio_program_id','day_of_week','starts_at','ends_at','is_live','is_active'];protected function casts():array{return['day_of_week'=>'integer','is_live'=>'boolean','is_active'=>'boolean'];}public function program():BelongsTo{return $this->belongsTo(RadioProgram::class,'radio_program_id');}}
