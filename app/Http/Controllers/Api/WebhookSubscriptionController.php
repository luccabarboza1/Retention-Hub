<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\WebhookSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebhookSubscriptionController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(WebhookSubscription::whereNull('deleted_at')->orderByDesc('created_at')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'         => 'required|string|max:100',
            'url'          => 'required|url|max:2048',
            'trigger_type' => 'required|in:card.created,card.updated,card.finished,customer.updated',
            'description'  => 'nullable|string',
            'is_active'    => 'nullable|boolean',
        ]);

        $subscription = WebhookSubscription::create([
            ...$data,
            'secret' => Str::random(64),
        ]);

        return response()->json([
            ...$subscription->toArray(),
            'secret' => $subscription->secret,
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $subscription = WebhookSubscription::whereNull('deleted_at')->findOrFail($id);

        $data = $request->validate([
            'name'        => 'sometimes|string|max:100',
            'url'         => 'sometimes|url|max:2048',
            'description' => 'nullable|string',
            'is_active'   => 'nullable|boolean',
        ]);

        $subscription->update($data);

        return response()->json($subscription);
    }

    public function destroy(int $id): JsonResponse
    {
        WebhookSubscription::whereNull('deleted_at')->findOrFail($id)->delete();

        return response()->json(null, 204);
    }
}
