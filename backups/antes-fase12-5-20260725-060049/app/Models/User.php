<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasFactory, HasRoles, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'is_active',
        'last_login_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'is_active' => 'boolean',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    public function socialAccounts(): HasMany
    {
        return $this->hasMany(SocialAccount::class);
    }

    public function subscription(): HasOne
    {
        return $this->hasOne(Subscription::class);
    }

    public function journalistProfile(): HasOne
    {
        return $this->hasOne(JournalistProfile::class);
    }

    public function columnistProfile(): HasOne
    {
        return $this->hasOne(ColumnistProfile::class);
    }

    public function advertiserProfile(): HasOne
    {
        return $this->hasOne(AdvertiserProfile::class);
    }

    public function authoredArticles(): HasMany
    {
        return $this->hasMany(NewsArticle::class, 'author_id');
    }

    public function publishedArticles(): HasMany
    {
        return $this->hasMany(NewsArticle::class, 'published_by');
    }

    public function adCampaigns(): HasMany
    {
        return $this->hasMany(AdCampaign::class, 'advertiser_id');
    }

    public function vehicleListings(): HasMany
    {
        return $this->hasMany(VehicleListing::class);
    }

    public function realEstateListings(): HasMany
    {
        return $this->hasMany(RealEstateListing::class);
    }

    public function hasAdFreeAccess(): bool
    {
        return $this->subscription?->isActive() === true
            || $this->hasAnyRole(['Patrocinador']);
    }
}
