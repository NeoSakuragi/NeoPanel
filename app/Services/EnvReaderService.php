<?php

namespace App\Services;

class EnvReaderService
{
    private const DEFAULT_KEYS = [
        'APP_NAME', 'APP_ENV', 'APP_URL', 'APP_DEBUG',
        'DB_CONNECTION', 'DB_DATABASE',
    ];

    private const SENSITIVE_PATTERNS = [
        '/PASSWORD/i', '/SECRET/i', '/KEY$/i', '/TOKEN/i',
    ];

    public function read(string $path, ?array $keys = null): ?array
    {
        $envFile = rtrim($path, '/') . '/.env';

        if (!file_exists($envFile) || !is_readable($envFile)) {
            return null;
        }

        $keys = $keys ?? self::DEFAULT_KEYS;
        $result = [];

        foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);
            if (str_starts_with($line, '#') || !str_contains($line, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $line, 2);
            $key = trim($key);
            $value = trim($value, " \t\"'");

            if (in_array($key, $keys, true) && !$this->isSensitive($key)) {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    private function isSensitive(string $key): bool
    {
        if ($key === 'APP_KEY') return true;

        foreach (self::SENSITIVE_PATTERNS as $pattern) {
            if (preg_match($pattern, $key)) return true;
        }

        return false;
    }
}
