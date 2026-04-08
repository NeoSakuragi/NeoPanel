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
            ->whereIn('status', ['pending', 'running'])
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

        // Run in background so the HTTP response returns immediately
        // PHP_BINARY points to php-fpm in FPM context, use php CLI instead
        $php = '/usr/bin/php' . PHP_MAJOR_VERSION . '.' . PHP_MINOR_VERSION;
        $artisan = base_path('artisan');
        $cmd = "{$php} {$artisan} deploy:run {$deployment->id} > /dev/null 2>&1 &";
        exec($cmd);

        return response()->json([
            'deployment' => $deployment,
        ]);
    }

    public function status(Deployment $deployment): JsonResponse
    {
        return response()->json([
            'deployment' => $deployment->fresh(),
        ]);
    }

    public function history(Instance $instance): JsonResponse
    {
        return response()->json([
            'deployments' => $instance->deployments()->with('user:id,name')->limit(20)->get(),
        ]);
    }
}
