<?php

namespace App\Services;

use App\Models\Deployment;
use App\Models\Instance;
use Illuminate\Support\Facades\Process;

class DeployService
{
    public function getAvailableTags(Instance $instance): array
    {
        Process::path($instance->path)->run('git fetch origin --tags 2>&1');

        $result = Process::path($instance->path)->run("git tag -l --sort=-version:refname --format='%(refname:short)||%(contents:subject)'");

        if (!$result->successful()) {
            return [];
        }

        return array_values(array_filter(
            array_map(function ($line) {
                $parts = explode('||', $line, 2);
                if (empty($parts[0])) return null;
                return [
                    'name' => trim($parts[0]),
                    'message' => trim($parts[1] ?? ''),
                ];
            }, explode("\n", trim($result->output()))),
        ));
    }

    public function getCurrentTag(Instance $instance): ?string
    {
        $result = Process::path($instance->path)->run('git describe --tags --exact-match HEAD 2>/dev/null');

        return $result->successful() ? trim($result->output()) : null;
    }

    public function execute(Deployment $deployment): void
    {
        $instance = $deployment->instance;
        $application = $instance->application;
        $recipe = $application?->deploy_recipe;

        if (!$recipe || empty($recipe)) {
            $deployment->update([
                'status' => 'failed',
                'output' => "No deploy recipe configured for this application.\n",
                'finished_at' => now(),
            ]);
            return;
        }

        $deployment->update([
            'status' => 'running',
            'started_at' => now(),
            'output' => '',
        ]);

        $output = '';

        foreach ($recipe as $step) {
            $cmd = str_replace('{{tag}}', $deployment->tag, $step);

            $output .= "$ {$cmd}\n";
            $deployment->update(['output' => $output]);

            $result = Process::path($instance->path)
                ->timeout(300)
                ->run($cmd);

            $stepOutput = trim($result->output() . "\n" . $result->errorOutput());
            if ($stepOutput) {
                $output .= $stepOutput . "\n";
            }

            if (!$result->successful()) {
                $output .= "\n✗ Failed (exit code {$result->exitCode()})\n";
                $deployment->update([
                    'status' => 'failed',
                    'output' => $output,
                    'finished_at' => now(),
                ]);
                return;
            }

            $output .= "✓ Done\n\n";
            $deployment->update(['output' => $output]);
        }

        $output .= "Deployment complete.\n";
        $deployment->update([
            'status' => 'success',
            'output' => $output,
            'finished_at' => now(),
        ]);
    }
}
