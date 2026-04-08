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
            'instance' => $instance,
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
