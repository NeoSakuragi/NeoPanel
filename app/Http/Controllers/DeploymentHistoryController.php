<?php

namespace App\Http\Controllers;

use App\Models\Deployment;
use Inertia\Inertia;
use Inertia\Response;

class DeploymentHistoryController extends Controller
{
    public function index(): Response
    {
        $deployments = Deployment::with(['instance.application', 'user'])
            ->orderByDesc('created_at')
            ->paginate(25);

        return Inertia::render('Deployments/Index', [
            'deployments' => $deployments,
        ]);
    }

    public function show(Deployment $deployment): Response
    {
        $deployment->load(['instance.application', 'user']);

        return Inertia::render('Deployments/Show', [
            'deployment' => $deployment,
        ]);
    }
}
