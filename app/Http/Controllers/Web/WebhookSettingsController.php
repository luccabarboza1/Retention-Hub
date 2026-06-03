<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\WebhookSubscription;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WebhookSettingsController extends Controller
{
    public function index()
    {
        $webhooks = WebhookSubscription::whereNull('deleted_at')
            ->orderByDesc('created_at')
            ->get();

        return view('settings.webhooks', compact('webhooks'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'          => 'required|string|max:100',
            'url'           => 'required|url|max:2048',
            'trigger_types' => 'required|array|min:1',
            'trigger_types.*' => 'required|string|in:card.created,card.updated,card.finished,customer.updated,*',
            'description'   => 'nullable|string|max:500',
            'is_active'     => 'nullable|boolean',
        ]);

        $data['trigger_types'] = array_values(array_unique($data['trigger_types']));
        $secret = Str::random(64);

        $webhook = WebhookSubscription::create([
            ...$data,
            'secret'    => $secret,
            'is_active' => $data['is_active'] ?? true,
        ]);

        return redirect()->route('settings.webhooks')
            ->with('webhook_created', [
                'id'     => $webhook->id,
                'secret' => $secret,
                'name'   => $webhook->name,
            ]);
    }

    public function update(WebhookSubscription $webhook, Request $request)
    {
        $data = $request->validate([
            'name'          => 'sometimes|string|max:100',
            'url'           => 'sometimes|url|max:2048',
            'trigger_types' => 'sometimes|array|min:1',
            'trigger_types.*' => 'required_with:trigger_types|string|in:card.created,card.updated,card.finished,customer.updated,*',
            'description'   => 'nullable|string|max:500',
            'is_active'     => 'nullable|boolean',
        ]);

        if (isset($data['trigger_types'])) {
            $data['trigger_types'] = array_values(array_unique($data['trigger_types']));
        }

        $webhook->update($data);

        return redirect()->route('settings.webhooks')->with('success', 'Webhook atualizado.');
    }

    public function destroy(WebhookSubscription $webhook)
    {
        $webhook->delete();
        return redirect()->route('settings.webhooks')->with('success', 'Webhook removido.');
    }
}
