<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Instance;
use App\Services\InstanceInfoService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(InstanceInfoService $service): Response
    {
        $applications = Application::active()->ordered()
            ->with(['instances' => fn ($q) => $q->active()])
            ->get()
            ->map(function (Application $app) use ($service) {
                return [
                    'application' => array_merge($app->toArray(), [
                        'has_auth' => $app->hasAuth(),
                    ]),
                    'instances' => $app->instances->map(fn (Instance $i) => $service->getStatus($i))->values()->all(),
                ];
            });

        // Also include ungrouped instances (no application_id)
        $ungrouped = Instance::active()->whereNull('application_id')->ordered()->get();
        $ungroupedStatuses = $ungrouped->map(fn (Instance $i) => $service->getStatus($i))->values()->all();

        return Inertia::render('Dashboard', [
            'applications' => $applications,
            'ungrouped' => $ungroupedStatuses,
        ]);
    }
}
