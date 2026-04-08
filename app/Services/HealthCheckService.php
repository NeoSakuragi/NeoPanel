<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class HealthCheckService
{
    public function check(string $url): array
    {
        try {
            $start = microtime(true);
            $response = Http::timeout(5)->connectTimeout(3)->get(rtrim($url, '/') . '/up');
            $elapsed = round((microtime(true) - $start) * 1000);

            return [
                'status' => $response->successful() ? 'up' : 'down',
                'status_code' => $response->status(),
                'response_time_ms' => $elapsed,
            ];
        } catch (\Throwable) {
            return [
                'status' => 'down',
                'status_code' => null,
                'response_time_ms' => null,
            ];
        }
    }
}
