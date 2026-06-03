<?php

namespace App\Http\Controllers\Api;

use App\Events\CustomerUpdated;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $customers = Customer::query()
            ->when($request->get('search'), fn ($q, $s) =>
                $q->where('company_name', 'like', "%{$s}%")
                  ->orWhere('client_name', 'like', "%{$s}%")
                  ->orWhere('email', 'like', "%{$s}%")
            )
            ->when($request->get('tier'), fn ($q, $tier) => $q->where('tier', $tier))
            ->when($request->get('segment'), fn ($q, $seg) => $q->where('segment', $seg))
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 20));

        return response()->json($customers);
    }

    public function show(int $id): JsonResponse
    {
        $customer = Customer::with(['products', 'cards'])->findOrFail($id);

        return response()->json($customer);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'client_name'               => 'required|string|max:255',
            'company_name'              => 'required|string|max:255',
            'segment'                   => 'nullable|string|max:100',
            'company_size'              => 'nullable|string|max:50',
            'instagram_followers_count' => 'nullable|integer|min:0',
            'email'                     => 'nullable|email|max:255',
            'monthly_fee'               => 'nullable|numeric|min:0',
            'contracted_at'             => 'nullable|date',
            'canceled_at'               => 'nullable|date',
            'tier'                      => 'nullable|string|max:50',
            'channel_type'              => 'nullable|string|max:50',
            'plan_name'                 => 'nullable|string|max:100',
            'has_chatbot'               => 'nullable|boolean',
            'has_ai'                    => 'nullable|boolean',
            'has_implementation'        => 'nullable|boolean',
        ]);

        $customer = Customer::create($data);

        return response()->json($customer, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);

        $data = $request->validate([
            'client_name'               => 'sometimes|string|max:255',
            'company_name'              => 'sometimes|string|max:255',
            'segment'                   => 'nullable|string|max:100',
            'company_size'              => 'nullable|string|max:50',
            'instagram_followers_count' => 'nullable|integer|min:0',
            'email'                     => 'nullable|email|max:255',
            'monthly_fee'               => 'nullable|numeric|min:0',
            'contracted_at'             => 'nullable|date',
            'canceled_at'               => 'nullable|date',
            'tier'                      => 'nullable|string|max:50',
            'channel_type'              => 'nullable|string|max:50',
            'plan_name'                 => 'nullable|string|max:100',
            'has_chatbot'               => 'nullable|boolean',
            'has_ai'                    => 'nullable|boolean',
            'has_implementation'        => 'nullable|boolean',
        ]);

        $customer->update($data);

        event(new CustomerUpdated($customer->fresh()));

        return response()->json($customer);
    }

    public function destroy(int $id): JsonResponse
    {
        Customer::findOrFail($id)->delete();

        return response()->json(null, 204);
    }
}
