<?php

namespace App\Services;

use Illuminate\Support\Facades\Process;

class GitService
{
    public function getInfo(string $path): ?array
    {
        if (!is_dir($path . '/.git')) {
            return null;
        }

        $run = fn (string $cmd) => Process::path($path)->run($cmd);

        $branch = trim($run('git rev-parse --abbrev-ref HEAD')->output());
        $log = $run('git log -1 --format=%h||%H||%s||%ai||%an');
        $logParts = explode('||', trim($log->output()));

        $dirty = trim($run('git status --porcelain')->output()) !== '';

        $ahead = 0;
        $behind = 0;
        $revList = $run('git rev-list --left-right --count HEAD...@{upstream} 2>/dev/null');
        if ($revList->successful() && trim($revList->output()) !== '') {
            $parts = preg_split('/\s+/', trim($revList->output()));
            if (count($parts) === 2) {
                $ahead = (int) $parts[0];
                $behind = (int) $parts[1];
            }
        }

        $remoteUrl = trim($run('git config --get remote.origin.url')->output()) ?: null;

        return [
            'branch' => $branch,
            'commit_hash' => $logParts[0] ?? '',
            'commit_hash_full' => $logParts[1] ?? '',
            'commit_message' => $logParts[2] ?? '',
            'commit_date' => $logParts[3] ?? '',
            'commit_author' => $logParts[4] ?? '',
            'is_dirty' => $dirty,
            'ahead' => $ahead,
            'behind' => $behind,
            'remote_url' => $remoteUrl,
        ];
    }
}
