<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\BelongsTo;use Illuminate\Database\Eloquent\Relations\MorphTo;
class AgencyApprovalAction extends Model{
 protected $fillable=['approvable_type','approvable_id','action','from_status','to_status','note','user_id'];
 public function approvable():MorphTo{return $this->morphTo();}public function user():BelongsTo{return $this->belongsTo(User::class);}
}
