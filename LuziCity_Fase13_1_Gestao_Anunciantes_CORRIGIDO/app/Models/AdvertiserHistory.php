<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class AdvertiserHistory extends Model
{
    protected $fillable = ['advertiser_profile_id','user_id','type','title','description','occurred_at'];
    protected function casts(): array { return ['occurred_at'=>'datetime']; }
    public function advertiser(): BelongsTo { return $this->belongsTo(AdvertiserProfile::class, 'advertiser_profile_id'); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
}
