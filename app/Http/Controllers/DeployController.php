<?php

namespace App\Http\Controllers;

use App\Models\Deployment;
use App\Models\Instance;
use App\Services\DeployService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DeployController extends Controller
{
    public function tags(Instance $instance, DeployService $service): JsonResponse
    {
        return response()->json([
            'tags' => $service->getAvailableTags($instance),
            'current' => $service->getCurrentTag($instance),
        ]);
    }

    public function deploy(Request $request, Instance $instance, DeployService $service): JsonResponse
    {
        $validated = $request->validate([
            'tag' => 'required|string|max:100',
        ]);

        // Check for running deployment on this instance
        $running = Deployment::where('instance_id', $instance->id)
            ->where('status', 'running')
            ->exists();

        if ($running) {
            return response()->json(['error' => 'A deployment is already running on this instance.'], 409);
        }

        $currentTag = $service->getCurrentTag($instance);

        $deployment = Deployment::create([
            'instance_id' => $instance->id,
            'user_id' => $request->user()->id,
            'tag' => $validated['tag'],
            'previous_tag' => $currentTag,
            'status' => 'pending',
        ]);

        // Execute synchronously (for now — could be queued later)
        $service->execute($deployment);

        return response()->json([
            'deployment' => $deployment->fresh(),
        ]);
    }

    public function status(Deployment $deployment): JsonResponse
    {
        return response()->json([
            'deployment' => $deployment,
        ]);
    }

    public function history(Instance $instance): JsonResponse
    {
        return response()->json([
            'deployments' => $instance->deployments()->with('user:id,name')->limit(20)->get(),
        ]);
    }
}
