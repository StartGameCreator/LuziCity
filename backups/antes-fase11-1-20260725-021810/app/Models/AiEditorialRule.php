<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AiEditorialRule extends Model {
 protected $fillable=['profile_id','name','rule_type','instruction','priority','active'];
 protected function casts():array{return ['active'=>'boolean','priority'=>'integer'];}
 public function profile():BelongsTo{return $this->belongsTo(AiEditorialProfile::class,'profile_id');}
}
