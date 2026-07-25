<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AdvertiserAddress extends Model
{
    protected $fillable = ['advertiser_profile_id','type','postal_code','street','number','complement','district','city','state'];
    public function advertiser(): BelongsTo { return $this->belongsTo(AdvertiserProfile::class, 'advertiser_profile_id'); }
}
