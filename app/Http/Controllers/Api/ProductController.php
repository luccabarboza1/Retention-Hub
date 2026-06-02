<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\ProductChange;
use App\Services\ProductChangeClassifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    public function __construct(private readonly ProductChangeClassifier $classifier) {}

    public function index(Request $request): JsonResponse
    {
        $products = Product::query()
            ->when($request->get('customer_id'), fn ($q, $id) => $q->where('customer_id', $id))
            ->when($request->get('product_type'), fn ($q, $t) => $q->where('product_type', $t))
            ->when($request->get('status'), fn ($q, $s) => $q->where('status', $s))
            ->with('customer')
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 20));

        return response()->json($products);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(Product::with(['customer', 'changes'])->findOrFail($id));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_id'         => 'required|integer|exists:customers,id',
            'external_id'         => 'required|string|max:255',
            'contract_identifier' => 'nullable|string|max:255',
            'product_type'        => 'required|in:Host,Talk2',
            'consumption'         => 'nullable|numeric|min:0',
            'status'              => 'nullable|in:ativo,cancelado',
            'external_created_at' => 'nullable|date',
        ]);

        $product = Product::create($data);

        return response()->json($product, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $original = $product->only(['status', 'consumption']);

        $data = $request->validate([
            'contract_identifier' => 'nullable|string|max:255',
            'consumption'         => 'nullable|numeric|min:0',
            'status'              => 'nullable|in:ativo,cancelado',
            'external_created_at' => 'nullable|date',
        ]);

        $product->update($data);

        $changeType = $this->classifier->classify($product, $original);

        if ($changeType !== null) {
            ProductChange::create([
                'customer_id'       => $product->customer_id,
                'product_id'        => $product->id,
                'change_type'       => $changeType,
                'delta_consumption' => $this->classifier->deltaConsumption($product, $original),
            ]);
        }

        return response()->json($product);
    }

    public function destroy(int $id): JsonResponse
    {
        Product::findOrFail($id)->delete();

        return response()->json(null, 204);
    }
}
