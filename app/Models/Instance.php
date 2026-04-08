<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instance extends Model
{
    protected $fillable = [
        'name', 'path', 'url', 'github_repo', 'description',
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

    public function hasAuth(): bool
    {
        return $this->auth_secret && $this->login_profiles;
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
