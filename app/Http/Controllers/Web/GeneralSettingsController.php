<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class GeneralSettingsController extends Controller
{
    public function index()
    {
        $settings = [
            'customer_lookup_url' => AppSetting::get('customer_lookup_url', config('app.customer_lookup_url')),
        ];

        return view('settings.general', compact('settings'));
    }

    public function saveCustomerOptions(string $type, Request $request): JsonResponse
    {
        $allowed = ['tiers', 'segments'];
        abort_unless(in_array($type, $allowed), 404);

        $options = $request->validate(['options' => 'present|array'])['options'];
        $options = array_values(array_filter(array_unique(array_map('trim', $options))));

        AppSetting::set("customer_{$type}", json_encode($options));

        return response()->json(['ok' => true, 'options' => $options]);
    }

    public function saveCardOptions(string $type, Request $request): JsonResponse
    {
        $allowed = ['ombudsman_agents', 'ticket_origins', 'responsible_teams'];
        abort_unless(in_array($type, $allowed), 404);

        $options = $request->validate(['options' => 'present|array'])['options'];
        $options = array_values(array_filter(array_unique(array_map('trim', $options))));

        AppSetting::set("card_{$type}", json_encode($options));

        return response()->json(['ok' => true, 'options' => $options]);
    }

    public function checkUsage(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type'   => 'required|string',
            'option' => 'required|string',
        ]);

        $type = $data['type'];
        $option = $data['option'];

        $count = 0;
        $entityType = 'elementos';

        if (in_array($type, ['ombudsman_agents', 'ticket_origins', 'responsible_teams'])) {
            $column = match($type) {
                'ombudsman_agents' => 'ombudsman_agent',
                'ticket_origins'   => 'ticket_origin',
                'responsible_teams' => 'responsible_team',
            };
            $count = \App\Models\Card::where($column, $option)->count();
            $entityType = 'cards';
        } elseif (in_array($type, ['tiers', 'segments'])) {
            $column = match($type) {
                'tiers'    => 'tier',
                'segments' => 'segment',
            };
            $count = \App\Models\Customer::where($column, $option)->count();
            $entityType = 'clientes';
        } else {
            abort(400, 'Tipo inválido');
        }

        return response()->json([
            'count'      => $count,
            'entityType' => $entityType,
        ]);
    }

    public function deleteAndReplace(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type'        => 'required|string',
            'option'      => 'required|string',
            'action'      => 'required|in:clear,replace',
            'replacement' => 'nullable|string',
        ]);

        $type = $data['type'];
        $option = $data['option'];
        $action = $data['action'];
        $replacement = $data['replacement'];

        if (in_array($type, ['ombudsman_agents', 'ticket_origins', 'responsible_teams'])) {
            $column = match($type) {
                'ombudsman_agents' => 'ombudsman_agent',
                'ticket_origins'   => 'ticket_origin',
                'responsible_teams' => 'responsible_team',
            };
            $newValue = ($action === 'replace') ? $replacement : null;
            \App\Models\Card::where($column, $option)->update([$column => $newValue]);

            $settingKey = "card_{$type}";
            $stored = json_decode(AppSetting::get($settingKey, '[]'), true) ?: [];
            $stored = array_values(array_filter($stored, fn($o) => $o !== $option));
            AppSetting::set($settingKey, json_encode($stored));

        } elseif (in_array($type, ['tiers', 'segments'])) {
            $column = match($type) {
                'tiers'    => 'tier',
                'segments' => 'segment',
            };
            $newValue = ($action === 'replace') ? $replacement : null;
            \App\Models\Customer::where($column, $option)->update([$column => $newValue]);

            $settingKey = "customer_{$type}";
            $stored = json_decode(AppSetting::get($settingKey, '[]'), true) ?: [];
            $stored = array_values(array_filter($stored, fn($o) => $o !== $option));
            AppSetting::set($settingKey, json_encode($stored));
        } else {
            abort(400, 'Tipo inválido');
        }

        return response()->json(['ok' => true]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'customer_lookup_url' => 'nullable|url|max:2048',
        ]);

        AppSetting::set('customer_lookup_url', $data['customer_lookup_url'] ?? null);

        return redirect()->route('settings.general')->with('success', 'Configurações salvas.');
    }
}
