<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Customer;

class CustomerWebController extends Controller
{
    public function index()
    {
        $search    = request('q');
        $customers = Customer::query()
            ->when($search, fn ($q) => $q->where('company_name', 'like', "%$search%")
                ->orWhere('client_name', 'like', "%$search%")
                ->orWhere('email', 'like', "%$search%"))
            ->withCount(['cards', 'cards as open_cards_count' => fn ($q) => $q->whereIn('status', ['Aberto', 'Em Andamento'])])
            ->orderBy('company_name')
            ->paginate(30)
            ->withQueryString();

        return view('customers.index', compact('customers', 'search'));
    }

    public function show(Customer $customer)
    {
        $customer->loadCount(['cards', 'cards as open_cards_count' => fn ($q) => $q->whereIn('status', ['Aberto', 'Em Andamento'])]);
        $recentCards = $customer->cards()->with('product')->orderBy('started_at', 'desc')->limit(5)->get();
        return view('customers.show', compact('customer', 'recentCards'));
    }

    public function create()
    {
        return view('customers.create');
    }

    public function store()
    {
        $data = $this->validated();
        $customer = Customer::create($data);
        return redirect()->route('customers.show', $customer)->with('success', 'Cliente cadastrado com sucesso.');
    }

    public function update(Customer $customer)
    {
        $data = $this->validated();
        $customer->update($data);
        return redirect()->route('customers.show', $customer)->with('success', 'Cliente atualizado.');
    }

    public function cards(Customer $customer)
    {
        $cards = $customer->cards()->with('product')->orderBy('started_at', 'desc')->get();
        return view('customers.cards', compact('customer', 'cards'));
    }

    private function validated(): array
    {
        return request()->validate([
            'company_name'              => 'required|string|max:255',
            'client_name'               => 'required|string|max:255',
            'email'                     => 'nullable|email|max:255',
            'segment'                   => 'nullable|string|max:100',
            'company_size'              => 'nullable|string|max:50',
            'tier'                      => 'nullable|string|max:50',
            'channel_type'              => 'nullable|string|max:50',
            'plan_name'                 => 'nullable|string|max:100',
            'monthly_fee'               => 'nullable|numeric|min:0',
            'contracted_at'             => 'nullable|date',
            'canceled_at'               => 'nullable|date',
            'instagram_followers_count' => 'nullable|integer|min:0',
            'has_chatbot'               => 'boolean',
            'has_ai'                    => 'boolean',
            'has_implementation'        => 'boolean',
        ]);
    }
}
