<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Site extends Model
{
    protected $fillable = [
        'name', 'slug', 'city', 'state', 'logo_path', 'favicon_path',
        'theme_primary', 'theme_secondary', 'theme_background_path', 'is_active', 'is_default',
    ];

    protected function casts(): array
    {
        return ['is_active' => 'boolean', 'is_default' => 'boolean'];
    }

    public function domains(): HasMany
    {
        return $this->hasMany(SiteDomain::class);
    }

    public function settings(): HasMany
    {
        return $this->hasMany(SiteSetting::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withPivot(['permissions', 'is_active'])->withTimestamps();
    }

    public function setting(string $key, mixed $default = null): mixed
    {
        $settings = $this->relationLoaded('settings') ? $this->settings : $this->settings()->get();

        return $settings->firstWhere('key', $key)?->value ?? $default;
    }

    public static function current(): ?self
    {
        return app()->bound('currentSite') ? app('currentSite') : null;
    }
}
