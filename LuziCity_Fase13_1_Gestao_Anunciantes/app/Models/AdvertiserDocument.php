<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AdvertiserDocument extends Model
{
    protected $fillable = ['advertiser_profile_id','uploaded_by','type','name','path','mime_type','size_bytes'];
    public function advertiser(): BelongsTo { return $this->belongsTo(AdvertiserProfile::class, 'advertiser_profile_id'); }
    public function uploader(): BelongsTo { return $this->belongsTo(User::class, 'uploaded_by'); }
}
