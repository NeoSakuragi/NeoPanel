<?php

namespace App\Http\Controllers;

use App\Services\InstanceInfoService;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function __invoke(InstanceInfoService $service): Response
    {
        return Inertia::render('Dashboard', [
            'instances' => $service->getStatusForAll(),
        ]);
    }
}
