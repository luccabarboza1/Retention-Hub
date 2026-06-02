<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\AppSetting;
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

    public function update(Request $request)
    {
        $data = $request->validate([
            'customer_lookup_url' => 'nullable|url|max:2048',
        ]);

        AppSetting::set('customer_lookup_url', $data['customer_lookup_url'] ?? null);

        return redirect()->route('settings.general')->with('success', 'Configurações salvas.');
    }
}
