<?php
namespace App\Models;use Illuminate\Database\Eloquent\Model;use Illuminate\Database\Eloquent\Relations\BelongsTo;
class EditorialVerificationReview extends Model{protected $fillable=['editorial_source_claim_id','reviewed_by','decision','rationale','evidence_excerpt','alerts'];protected function casts():array{return['alerts'=>'array'];}public function claim():BelongsTo{return $this->belongsTo(EditorialSourceClaim::class,'editorial_source_claim_id');}public function reviewer():BelongsTo{return $this->belongsTo(User::class,'reviewed_by');}}
