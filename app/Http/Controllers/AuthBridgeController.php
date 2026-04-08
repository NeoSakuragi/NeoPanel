<?php

namespace App\Http\Controllers;

use App\Models\Instance;
use App\Services\AuthBridgeService;
use Illuminate\Http\RedirectResponse;

class AuthBridgeController extends Controller
{
    public function login(Instance $instance, string $profile, AuthBridgeService $service): RedirectResponse
    {
        $url = $service->generateLoginUrl($instance, $profile);

        if (!$url) {
            return back()->with('error', 'Login not configured for this instance.');
        }

        return redirect()->away($url);
    }

    public function generateSecret(): \Illuminate\Http\JsonResponse
    {
        return response()->json(['secret' => AuthBridgeService::generateSecret()]);
    }
}
