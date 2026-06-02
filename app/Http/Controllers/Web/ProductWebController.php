<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductChange;
use App\Services\ProductChangeClassifier;
use Illuminate\Http\Request;

class ProductWebController extends Controller
{
    public function __construct(private readonly ProductChangeClassifier $classifier) {}

    public function store(Customer $customer, Request $request)
    {
        $data = $request->validate([
            'external_id'         => 'required|string|max:255',
            'contract_identifier' => 'nullable|string|max:255',
            'product_type'        => 'required|in:Host,Talk2',
            'consumption'         => 'nullable|numeric|min:0',
            'status'              => 'nullable|in:ativo,cancelado',
            'external_created_at' => 'nullable|date',
        ]);

        $customer->products()->create($data);

        return redirect()->route('customers.show', $customer)->with('success', 'Produto adicionado com sucesso.');
    }

    public function update(Product $product, Request $request)
    {
        $original = $product->only(['status', 'consumption']);

        $data = $request->validate([
            'contract_identifier' => 'nullable|string|max:255',
            'consumption'         => 'nullable|numeric|min:0',
            'status'              => 'nullable|in:ativo,cancelado',
            'external_created_at' => 'nullable|date',
        ]);

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
}
