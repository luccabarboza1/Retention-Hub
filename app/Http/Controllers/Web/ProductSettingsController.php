<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ProductPlanConfig;
use Illuminate\Http\Request;

class ProductSettingsController extends Controller
{
    public function index()
    {
        $plans = ProductPlanConfig::orderBy('product_type')->orderBy('plan_name')->get();
        return view('settings.products', compact('plans'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'product_type'   => 'required|in:Talk2,Host',
            'plan_name'      => 'required|string|max:100',
            'price_per_unit' => 'required|numeric|min:0',
            'unit_label'     => 'required|string|max:50',
        ]);

        ProductPlanConfig::create($data);

        return redirect()->route('settings.products')->with('success', 'Plano criado.');
    }

    public function update(ProductPlanConfig $plan, Request $request)
    {
        $data = $request->validate([
            'plan_name'      => 'required|string|max:100',
            'price_per_unit' => 'required|numeric|min:0',
            'unit_label'     => 'required|string|max:50',
        ]);

        $plan->update($data);

        return redirect()->route('settings.products')->with('success', 'Plano atualizado.');
    }

    public function destroy(ProductPlanConfig $plan)
    {
        $plan->delete();
        return redirect()->route('settings.products')->with('success', 'Plano removido.');
    }
}
