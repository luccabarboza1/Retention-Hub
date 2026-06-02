<?php

namespace App\Http\Controllers\Api;

use App\Enums\WebhookTrigger;
use App\Http\Controllers\Controller;
use App\Models\WebhookSubscription;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Enum;

class WebhookSubscriptionController extends Controller
{
    public function index(): JsonResponse
    {
        return response()->json(WebhookSubscription::whereNull('deleted_at')->orderByDesc('created_at')->get());
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'            => 'required|string|max:100',
            'url'             => 'required|url|max:2048',
            'trigger_types'   => 'required|array|min:1',
            'trigger_types.*' => ['required', new Enum(WebhookTrigger::class)],
            'description'     => 'nullable|string',
            'is_active'       => 'nullable|boolean',
        ]);

        $subscription = WebhookSubscription::create([
            ...$data,
            'trigger_types' => array_values(array_unique($data['trigger_types'])),
            'secret'        => Str::random(64),
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
            'name'            => 'sometimes|string|max:100',
            'url'             => 'sometimes|url|max:2048',
            'trigger_types'   => 'sometimes|array|min:1',
            'trigger_types.*' => ['required_with:trigger_types', new Enum(WebhookTrigger::class)],
            'description'     => 'nullable|string',
            'is_active'       => 'nullable|boolean',
        ]);

        if (isset($data['trigger_types'])) {
            $data['trigger_types'] = array_values(array_unique($data['trigger_types']));
        }

        $subscription->update($data);

        return response()->json($subscription);
    }

    public function destroy(int $id): JsonResponse
    {
        WebhookSubscription::whereNull('deleted_at')->findOrFail($id)->delete();

        return response()->json(null, 204);
    }
}
