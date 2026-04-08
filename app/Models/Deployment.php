<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Deployment extends Model
{
    protected $fillable = [
        'instance_id', 'user_id', 'tag', 'previous_tag',
        'status', 'output', 'started_at', 'finished_at',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
        ];
    }

    public function instance(): BelongsTo
    {
        return $this->belongsTo(Instance::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
