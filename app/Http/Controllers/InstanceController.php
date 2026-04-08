<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Instance;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InstanceController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Instances/Index', [
            'instances' => Instance::with('application')->ordered()->get()->map(fn ($i) => array_merge($i->toArray(), [
                'has_auth' => $i->hasAuth(),
                'effective_login_profiles' => $i->getEffectiveLoginProfiles(),
            ])),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Instances/Create', [
            'applications' => Application::ordered()->get(['id', 'name']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'application_id' => 'nullable|exists:applications,id',
            'name' => 'required|string|max:255',
            'path' => 'required|string|max:500',
            'url' => 'nullable|url|max:500',
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
            'instance' => $instance,
            'applications' => Application::ordered()->get(['id', 'name']),
        ]);
    }

    public function update(Request $request, Instance $instance): RedirectResponse
    {
        $validated = $request->validate([
            'application_id' => 'nullable|exists:applications,id',
            'name' => 'required|string|max:255',
            'path' => 'required|string|max:500',
            'url' => 'nullable|url|max:500',
            'description' => 'nullable|string|max:1000',
            'environment' => 'required|in:production,staging,dev,custom',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        if (!is_dir($validated['path'])) {
            return back()->withErrors(['path' => 'Directory does not exist on disk.']);
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
