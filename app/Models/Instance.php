<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Instance extends Model
{
    protected $fillable = [
        'application_id', 'name', 'path', 'url', 'github_repo', 'description',
        'environment', 'sort_order', 'is_active', 'is_self', 'env_keys',
        'auth_secret', 'login_profiles',
    ];

    protected $hidden = ['auth_secret'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_self' => 'boolean',
            'sort_order' => 'integer',
            'env_keys' => 'array',
            'auth_secret' => 'encrypted',
            'login_profiles' => 'array',
        ];
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(Application::class);
    }

    public function getEffectiveAuthSecret(): ?string
    {
        return $this->auth_secret ?? $this->application?->auth_secret;
    }

    public function getEffectiveLoginProfiles(): ?array
    {
        return $this->login_profiles ?? $this->application?->login_profiles;
    }

    public function hasAuth(): bool
    {
        return (bool) $this->getEffectiveAuthSecret() && !empty($this->getEffectiveLoginProfiles());
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }
}
