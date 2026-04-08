<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Instance extends Model
{
    protected $fillable = [
        'name', 'path', 'url', 'github_repo', 'description',
        'environment', 'sort_order', 'is_active', 'is_self', 'env_keys',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_self' => 'boolean',
            'sort_order' => 'integer',
            'env_keys' => 'array',
        ];
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
