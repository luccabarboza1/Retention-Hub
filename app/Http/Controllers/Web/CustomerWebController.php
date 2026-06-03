<?php

namespace App\Http\Controllers\Web;

use App\Events\CustomerUpdated;
use App\Http\Controllers\Controller;
use App\Models\AppSetting;
use App\Models\Customer;
use App\Models\ProductPlanConfig;

class CustomerWebController extends Controller
{
    public function index()
    {
        $search    = request('q');
        $customers = Customer::query()
            ->when($search, fn ($q) => $q->where('company_name', 'like', "%$search%")
                ->orWhere('client_name', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%")
                ->orWhereRaw("JSON_SEARCH(related_emails, 'one', ?) IS NOT NULL", ["%$search%"]))
            ->withCount(['cards', 'cards as open_cards_count' => fn ($q) => $q->whereIn('status', ['Aberto', 'Em Andamento'])])
            ->orderBy('company_name')
            ->paginate(30)
            ->withQueryString();

        return view('customers.index', compact('customers', 'search'));
    }

    public function show(Customer $customer)
    {
        $customer->loadCount(['cards', 'cards as open_cards_count' => fn ($q) => $q->whereIn('status', ['Aberto', 'Em Andamento'])]);
        $customer->load(['products' => fn ($q) => $q->with(['changes' => fn ($q2) => $q2->orderByDesc('created_at')->limit(5)])->orderByDesc('created_at')]);
        $recentCards   = $customer->cards()->with('product')->orderBy('started_at', 'desc')->limit(5)->get();
        $recentChanges = \App\Models\ProductChange::where('customer_id', $customer->id)
            ->with('product')
            ->orderByDesc('created_at')
            ->limit(8)
            ->get();
        return view('customers.show', array_merge(compact('customer', 'recentCards', 'recentChanges'), $this->formOptions($customer)));
    }

    public function create()
    {
        return view('customers.create', $this->formOptions());
    }

    public function store()
    {
        $data = $this->validated();
        $data['instagram_followers_count'] ??= 0;
        $customer = Customer::create($data);

        // Cria produtos enviados no wizard (step 4)
        if (request()->has('products')) {
            foreach (request()->input('products', []) as $pData) {
                if (empty($pData['product_type']) || empty($pData['external_id'])) continue;

                if ($pData['product_type'] === 'Talk2' && !empty($pData['plan_name']) && !empty($pData['attendants_count'])) {
                    $plan = ProductPlanConfig::where('product_type', 'Talk2')
                        ->where('plan_name', $pData['plan_name'])->first();
                    if ($plan) {
                        $pData['consumption'] = $plan->price_per_unit * (int) $pData['attendants_count'];
                    }
                }

                $customer->products()->create([
                    'external_id'         => $pData['external_id'] ?? null,
                    'contract_identifier' => $pData['contract_identifier'] ?? null,
                    'product_type'        => $pData['product_type'],
                    'plan_name'           => $pData['plan_name'] ?? null,
                    'attendants_count'    => $pData['attendants_count'] ?? null,
                    'host_services'       => $pData['host_services'] ?? null,
                    'consumption'         => $pData['consumption'] ?? null,
                    'status'              => $pData['status'] ?? 'ativo',
                    'has_chatbot'         => !empty($pData['has_chatbot']),
                    'has_ai'              => !empty($pData['has_ai']),
                    'has_implementation'  => !empty($pData['has_implementation']),
                    'external_created_at' => $pData['external_created_at'] ?? null,
                ]);
            }
        }

        event(new CustomerUpdated($customer));
        return redirect()->route('customers.show', $customer)->with('success', 'Cliente cadastrado com sucesso.');
    }

    public function update(Customer $customer)
    {
        $data = $this->validated($customer);
        $data['instagram_followers_count'] ??= 0;
        $customer->update($data);
        event(new CustomerUpdated($customer->fresh()));
        return redirect()->route('customers.show', $customer)->with('success', 'Cliente atualizado.');
    }

    public function cards(Customer $customer)
    {
        $cards = $customer->cards()->with('product')->orderBy('started_at', 'desc')->get();
        return view('customers.cards', compact('customer', 'cards'));
    }

    public function destroy(Customer $customer)
    {
        $customer->delete();
        return redirect()->route('customers.index')->with('success', 'Cliente excluído com sucesso.');
    }

    private function validated(?Customer $customer = null): array
    {
        return request()->validate([
            'company_name'              => 'required|string|max:255',
            'client_name'               => 'required|string|max:255',
            'email'                     => 'nullable|email|max:255|unique:customers,email' . ($customer ? ',' . $customer->id : ''),
            'related_emails'            => 'nullable|array',
            'related_emails.*'          => 'email',
            'segment'                   => 'nullable|string|max:100',
            'company_size'              => 'nullable|string|max:50',
            'tier'                      => 'nullable|string|max:50',
            'channel_type'              => 'nullable|string|max:50',
            'plan_name'                 => 'nullable|string|max:100',
            'monthly_fee'               => 'nullable|numeric|min:0',
            'contracted_at'             => 'nullable|date',
            'canceled_at'               => 'nullable|date',
            'instagram_followers_count' => 'nullable|integer|min:0',
        ]);
    }

    private function formOptions(?Customer $customer = null): array
    {
        $planConfigs = ProductPlanConfig::orderBy('product_type')->orderBy('plan_name')->get();

        $storedTiers = AppSetting::get('customer_tiers');
        if ($storedTiers === null) {
            $dbTiers = Customer::whereNotNull('tier')->distinct()->pluck('tier')->toArray();
            $storedTiers = array_values(array_unique(array_merge(['Gold', 'Silver', 'Bronze', 'Premium', 'VIP'], $dbTiers)));
            AppSetting::set('customer_tiers', json_encode($storedTiers));
        } else {
            $storedTiers = json_decode($storedTiers, true) ?: [];
        }

        $storedSegments = AppSetting::get('customer_segments');
        if ($storedSegments === null) {
            $dbSegments = Customer::whereNotNull('segment')->distinct()->pluck('segment')->toArray();
            $storedSegments = array_values(array_unique(array_merge(['E-commerce', 'SaaS', 'Varejo', 'Serviços', 'Indústria', 'Fintech', 'EdTech'], $dbSegments)));
            AppSetting::set('customer_segments', json_encode($storedSegments));
        } else {
            $storedSegments = json_decode($storedSegments, true) ?: [];
        }

        $tiers = collect($storedTiers);
        if ($customer && $customer->tier && !$tiers->contains($customer->tier)) {
            $tiers->push($customer->tier);
        }
        $tiers = $tiers->unique()->sort()->values();

        $plans = Customer::whereNotNull('plan_name')->distinct()->orderBy('plan_name')->pluck('plan_name')
                        ->merge(['Host Básico', 'Host Pro', 'Host Enterprise', 'Talk2 Basic', 'Talk2 Pro'])->unique()->sort()->values();

        $segments = collect($storedSegments);
        if ($customer && $customer->segment && !$segments->contains($customer->segment)) {
            $segments->push($customer->segment);
        }
        $segments = $segments->unique()->sort()->values();

        $sizes    = collect(['Microempresa', 'Pequeno Porte', 'Médio Porte', 'Grande Porte', 'Enterprise']);
        $channels = Customer::whereNotNull('channel_type')->distinct()->orderBy('channel_type')->pluck('channel_type')
                        ->merge(['Inbound', 'Outbound', 'Indicação', 'Parceiro', 'RA', 'Marketplace'])->unique()->sort()->values();

        return compact('tiers', 'plans', 'segments', 'sizes', 'channels', 'planConfigs');
    }
}
