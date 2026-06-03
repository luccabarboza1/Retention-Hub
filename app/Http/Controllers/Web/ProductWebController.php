<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductChange;
use App\Models\ProductPlanConfig;
use App\Services\ProductChangeClassifier;
use Illuminate\Http\Request;

class ProductWebController extends Controller
{
    public function __construct(private readonly ProductChangeClassifier $classifier) {}

    public function store(Customer $customer, Request $request)
    {
        $type = $request->input('product_type');

        $data = $request->validate($this->rules($type));

        // Calcula consumption para Talk2: plano × atendentes
        if ($type === 'Talk2' && $data['plan_name'] && $data['attendants_count']) {
            $plan = ProductPlanConfig::where('product_type', 'Talk2')
                ->where('plan_name', $data['plan_name'])
                ->first();
            if ($plan) {
                $data['consumption'] = $plan->price_per_unit * $data['attendants_count'];
            }
        }

        $customer->products()->create($data);

        return redirect()->route('customers.show', $customer)->with('success', 'Produto adicionado com sucesso.');
    }

    public function update(Product $product, Request $request)
    {
        $original = $product->only(['status', 'consumption']);

        $data = $request->validate($this->rules($product->product_type, update: true));

        // Recalcula consumption para Talk2
        if ($product->product_type === 'Talk2') {
            $planName        = $data['plan_name'] ?? $product->plan_name;
            $attendantsCount = $data['attendants_count'] ?? $product->attendants_count;

            if ($planName && $attendantsCount) {
                $plan = ProductPlanConfig::where('product_type', 'Talk2')
                    ->where('plan_name', $planName)
                    ->first();
                if ($plan) {
                    $data['consumption'] = $plan->price_per_unit * $attendantsCount;
                }
            }
        }

        $product->update($data);
        $product->refresh();

        $changeType = $this->classifier->classify($product, $original);

        if ($changeType !== null) {
            ProductChange::create([
                'customer_id'       => $product->customer_id,
                'product_id'        => $product->id,
                'change_type'       => $changeType,
                'delta_consumption' => $this->classifier->deltaConsumption($product, $original),
            ]);
        }

        return redirect()->route('customers.show', $product->customer_id)->with('success', 'Produto atualizado.');
    }

    public function destroy(Product $product)
    {
        $customerId = $product->customer_id;
        $product->delete();

        return redirect()->route('customers.show', $customerId)->with('success', 'Produto removido.');
    }

    private function rules(string $type, bool $update = false): array
    {
        $sometimes = $update ? 'sometimes|' : '';

        $base = [
            'status'              => 'nullable|in:ativo,cancelado',
            'external_created_at' => 'nullable|date',
        ];

        if ($type === 'Talk2') {
            return array_merge($base, [
                'product_type'     => $update ? 'sometimes|in:Host,Talk2' : 'required|in:Host,Talk2',
                'external_id'      => $sometimes . 'required|string|max:255',
                'contract_identifier' => 'nullable|string|max:255',
                'plan_name'        => $sometimes . 'required|string|max:100',
                'attendants_count' => $sometimes . 'required|integer|min:1',
                'has_chatbot'      => 'boolean',
                'has_ai'           => 'boolean',
                'has_implementation' => 'boolean',
            ]);
        }

        // Host
        return array_merge($base, [
            'product_type'        => $update ? 'sometimes|in:Host,Talk2' : 'required|in:Host,Talk2',
            'external_id'         => $sometimes . 'required|string|max:255',
            'contract_identifier' => 'nullable|string|max:255',
            'consumption'         => 'nullable|numeric|min:0',
            'host_services'       => 'nullable|array',
            'host_services.*'     => 'in:email,dominio,hospedagem',
        ]);
    }
}
