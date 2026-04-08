<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Application extends Model
{
    protected $fillable = [
        'name', 'github_repo', 'description', 'auth_secret',
        'login_profiles', 'sort_order', 'is_active',
    ];

    protected $hidden = ['auth_secret'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'auth_secret' => 'encrypted',
            'login_profiles' => 'array',
        ];
    }

    public function instances(): HasMany
    {
        return $this->hasMany(Instance::class)->orderBy('sort_order')->orderBy('environment');
    }

    public function hasAuth(): bool
    {
        return (bool) $this->auth_secret && !empty($this->login_profiles);
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
