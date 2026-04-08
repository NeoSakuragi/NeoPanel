<?php

namespace App\Services;

use App\Models\Instance;

class AuthBridgeService
{
    public function generateLoginUrl(Instance $instance, string $profileKey): ?string
    {
        $secret = $instance->getEffectiveAuthSecret();
        $profiles = $instance->getEffectiveLoginProfiles();

        if (!$secret || !$profiles) {
            return null;
        }

        $profile = collect($profiles)->firstWhere('key', $profileKey);
        if (!$profile) {
            return null;
        }

        $payload = [
            'user' => $profile['user'],
            'guard' => $profile['guard'] ?? 'web',
            'expires' => time() + 60,
        ];

        $signature = hash_hmac('sha256', json_encode($payload), $secret);

        $query = http_build_query(array_merge($payload, ['signature' => $signature]));

        return rtrim($instance->url, '/') . '/neopanel/auth?' . $query;
    }

    public static function generateSecret(): string
    {
        return bin2hex(random_bytes(32));
    }
}
