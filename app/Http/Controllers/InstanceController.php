<?php

namespace App\Http\Controllers;

use App\Models\Instance;
use App\Services\InstanceInfoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InstanceController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Instances/Index', [
            'instances' => Instance::ordered()->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Instances/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'path' => 'required|string|max:500',
            'url' => 'nullable|url|max:500',
            'github_repo' => ['nullable', 'string', 'regex:/^[a-zA-Z0-9._-]+\/[a-zA-Z0-9._-]+$/'],
            'description' => 'nullable|string|max:1000',
            'environment' => 'required|in:production,staging,dev,custom',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        if (!is_dir($validated['path'])) {
            return back()->withErrors(['path' => 'Directory does not exist on disk.']);
        }

        Instance::create($validated);

        return redirect()->route('instances.index')
            ->with('success', 'Instance added.');
    }

    public function edit(Instance $instance): Response
    {
        return Inertia::render('Instances/Edit', [
            'instance' => array_merge($instance->toArray(), [
                'has_secret' => (bool) $instance->auth_secret,
            ]),
        ]);
    }

    public function update(Request $request, Instance $instance): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'path' => 'required|string|max:500',
            'url' => 'nullable|url|max:500',
            'github_repo' => ['nullable', 'string', 'regex:/^[a-zA-Z0-9._-]+\/[a-zA-Z0-9._-]+$/'],
            'description' => 'nullable|string|max:1000',
            'environment' => 'required|in:production,staging,dev,custom',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
            'auth_secret' => 'nullable|string|max:500',
            'login_profiles' => 'nullable|array',
            'login_profiles.*.key' => 'required|string|max:50',
            'login_profiles.*.label' => 'required|string|max:100',
            'login_profiles.*.user' => 'required|string|max:100',
            'login_profiles.*.guard' => 'required|string|max:50',
        ]);

        if (!is_dir($validated['path'])) {
            return back()->withErrors(['path' => 'Directory does not exist on disk.']);
        }

        // Don't overwrite secret if not provided (keep existing)
        if (empty($validated['auth_secret'])) {
            unset($validated['auth_secret']);
        }

        // Clean empty login profiles
        if (isset($validated['login_profiles'])) {
            $validated['login_profiles'] = array_values(array_filter(
                $validated['login_profiles'],
                fn ($p) => !empty($p['key']) && !empty($p['user']),
            ));
            if (empty($validated['login_profiles'])) {
                $validated['login_profiles'] = null;
            }
        }

        $instance->update($validated);

        return redirect()->route('instances.index')
            ->with('success', 'Instance updated.');
    }

    public function destroy(Instance $instance): RedirectResponse
    {
        if ($instance->is_self) {
            return back()->with('error', 'Cannot delete the panel itself.');
        }

        $instance->delete();

        return redirect()->route('instances.index')
            ->with('success', 'Instance removed.');
    }
}
