<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AdvertiserContact extends Model
{
    protected $fillable = ['advertiser_profile_id','name','position','phone','whatsapp','email','notes','is_primary'];
    protected function casts(): array { return ['is_primary'=>'boolean']; }
    public function advertiser(): BelongsTo { return $this->belongsTo(AdvertiserProfile::class, 'advertiser_profile_id'); }
}
