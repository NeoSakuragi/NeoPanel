<?php

namespace App\Http\Controllers;

use App\Models\Application;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ApplicationController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Applications/Index', [
            'applications' => Application::ordered()->withCount('instances')->get(),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Applications/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'github_repo' => ['nullable', 'string', 'regex:/^[a-zA-Z0-9._-]+\/[a-zA-Z0-9._-]+$/'],
            'description' => 'nullable|string|max:1000',
            'auth_secret' => 'nullable|string|max:500',
            'login_profiles' => 'nullable|array',
            'login_profiles.*.key' => 'required|string|max:50',
            'login_profiles.*.label' => 'required|string|max:100',
            'login_profiles.*.user' => 'required|string|max:100',
            'login_profiles.*.guard' => 'required|string|max:50',
            'deploy_recipe' => 'nullable|array',
            'deploy_recipe.*' => 'required|string|max:500',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        if (isset($validated['login_profiles'])) {
            $validated['login_profiles'] = array_values(array_filter(
                $validated['login_profiles'],
                fn ($p) => !empty($p['key']) && !empty($p['user']),
            ));
        }

        if (isset($validated['deploy_recipe'])) {
            $validated['deploy_recipe'] = array_values(array_filter($validated['deploy_recipe'], fn ($s) => trim($s) !== ''));
        }

        Application::create($validated);

        return redirect()->route('applications.index')
            ->with('success', 'Application created.');
    }

    public function edit(Application $application): Response
    {
        return Inertia::render('Applications/Edit', [
            'application' => array_merge($application->toArray(), [
                'has_secret' => (bool) $application->auth_secret,
            ]),
            'instances' => $application->instances()->get(),
        ]);
    }

    public function update(Request $request, Application $application): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'github_repo' => ['nullable', 'string', 'regex:/^[a-zA-Z0-9._-]+\/[a-zA-Z0-9._-]+$/'],
            'description' => 'nullable|string|max:1000',
            'auth_secret' => 'nullable|string|max:500',
            'login_profiles' => 'nullable|array',
            'login_profiles.*.key' => 'required|string|max:50',
            'login_profiles.*.label' => 'required|string|max:100',
            'login_profiles.*.user' => 'required|string|max:100',
            'login_profiles.*.guard' => 'required|string|max:50',
            'deploy_recipe' => 'nullable|array',
            'deploy_recipe.*' => 'required|string|max:500',
            'sort_order' => 'integer|min:0',
            'is_active' => 'boolean',
        ]);

        if (empty($validated['auth_secret'])) {
            unset($validated['auth_secret']);
        }

        if (isset($validated['login_profiles'])) {
            $validated['login_profiles'] = array_values(array_filter(
                $validated['login_profiles'],
                fn ($p) => !empty($p['key']) && !empty($p['user']),
            ));
            if (empty($validated['login_profiles'])) {
                $validated['login_profiles'] = null;
            }
        }

        if (isset($validated['deploy_recipe'])) {
            $validated['deploy_recipe'] = array_values(array_filter($validated['deploy_recipe'], fn ($s) => trim($s) !== ''));
            if (empty($validated['deploy_recipe'])) {
                $validated['deploy_recipe'] = null;
            }
        }

        $application->update($validated);

        return redirect()->route('applications.index')
            ->with('success', 'Application updated.');
    }

    public function destroy(Application $application): RedirectResponse
    {
        if ($application->instances()->exists()) {
            return back()->with('error', 'Remove all instances first.');
        }

        $application->delete();

        return redirect()->route('applications.index')
            ->with('success', 'Application removed.');
    }
}
