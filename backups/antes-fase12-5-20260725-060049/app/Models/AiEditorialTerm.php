<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AiEditorialTerm extends Model {
 protected $fillable=['profile_id','term','replacement','type','context','active'];
 protected function casts():array{return ['active'=>'boolean'];}
 public function profile():BelongsTo{return $this->belongsTo(AiEditorialProfile::class,'profile_id');}
}
