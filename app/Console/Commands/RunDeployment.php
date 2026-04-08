<?php

namespace App\Console\Commands;

use App\Models\Deployment;
use App\Services\DeployService;
use Illuminate\Console\Command;

class RunDeployment extends Command
{
    protected $signature = 'deploy:run {deployment}';

    protected $description = 'Execute a deployment in the background';

    public function handle(DeployService $service): int
    {
        $deployment = Deployment::findOrFail($this->argument('deployment'));

        if ($deployment->status !== 'pending') {
            $this->error("Deployment #{$deployment->id} is not pending (status: {$deployment->status}).");
            return 1;
        }

        $service->execute($deployment);

        return $deployment->fresh()->status === 'success' ? 0 : 1;
    }
}
