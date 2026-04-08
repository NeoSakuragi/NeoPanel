<?php

namespace App\Services;

use App\Models\Instance;
use Illuminate\Support\Collection;

class InstanceInfoService
{
    public function __construct(
        private GitService $git,
        private EnvReaderService $env,
        private HealthCheckService $health,
    ) {}

    public function getStatus(Instance $instance): array
    {
        $envKeys = $instance->env_keys;

        return [
            'instance' => array_merge($instance->toArray(), [
                'has_auth' => $instance->hasAuth(),
            ]),
            'git' => $this->git->getInfo($instance->path),
            'env' => $this->env->read($instance->path, $envKeys),
            'health' => $instance->url ? $this->health->check($instance->url, $instance->is_self) : null,
        ];
    }

    public function getStatusForAll(): array
    {
        $instances = Instance::active()->ordered()->get();

        return $instances->map(fn (Instance $i) => $this->getStatus($i))->values()->all();
    }
}
