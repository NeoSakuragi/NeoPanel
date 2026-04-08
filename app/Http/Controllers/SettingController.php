<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function edit(): Response
    {
        return Inertia::render('Settings/Edit', [
            'settings' => [
                'github_token' => Setting::get('github_token') ? '••••••••' : '',
                'refresh_interval' => Setting::get('refresh_interval', '30'),
            ],
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'github_token' => 'nullable|string|max:500',
            'refresh_interval' => 'required|integer|min:10|max:300',
        ]);

        if ($validated['github_token'] && $validated['github_token'] !== '••••••••') {
            Setting::set('github_token', $validated['github_token'], 'encrypted');
        } elseif (!$validated['github_token']) {
            Setting::where('key', 'github_token')->delete();
        }

        Setting::set('refresh_interval', (string) $validated['refresh_interval']);

        return back()->with('success', 'Settings saved.');
    }
}
