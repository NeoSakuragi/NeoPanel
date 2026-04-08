<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Instance;
use App\Services\InstanceInfoService;
use Illuminate\Http\JsonResponse;

class InstanceStatusController extends Controller
{
    public function index(InstanceInfoService $service): JsonResponse
    {
        return response()->json($service->getStatusForAll());
    }

    public function show(Instance $instance, InstanceInfoService $service): JsonResponse
    {
        return response()->json($service->getStatus($instance));
    }
}
