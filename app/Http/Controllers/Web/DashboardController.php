<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\CardComment;
use App\Models\Chat;
use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductChange;

class DashboardController extends Controller
{
    public function index()
    {
        // Clientes
        $totalCustomers  = Customer::count();
        $activeCustomers = Customer::whereNull('canceled_at')->count();
        $newThisMonth    = Customer::whereMonth('created_at', now()->month)
                                   ->whereYear('created_at', now()->year)->count();

        // Cards / Ouvidoria
        $totalCards    = Card::count();
        $openCards     = Card::whereIn('status', ['Aberto', 'Em Andamento'])->count();
        $retainedCards = Card::where('status', 'Retido')->whereNotNull('finished_at')->count();
        $churnCards    = Card::where('status', 'Churn')->whereNotNull('finished_at')->count();
        $closedCards   = $retainedCards + $churnCards;
        $retentionRate = $closedCards > 0 ? round($retainedCards / $closedCards * 100) : null;

        // Financeiro
        $totalMrr = Customer::whereNotNull('monthly_fee')->sum('monthly_fee');

        // Produtos
        $activeProducts   = Product::where('status', 'ativo')->count();
        $talk2Products    = Product::where('product_type', 'Talk2')->where('status', 'ativo')->count();
        $hostProducts     = Product::where('product_type', 'Host')->where('status', 'ativo')->count();
        $totalAttendants  = Product::where('product_type', 'Talk2')->where('status', 'ativo')->sum('attendants_count');

        // Atividade recente
        $recentCards   = Card::with('customer')->orderByDesc('created_at')->limit(8)->get();
        $recentChanges = ProductChange::with(['product', 'customer'])->orderByDesc('created_at')->limit(5)->get();

        // Busca universal (se houver query)
        $q         = trim(request('q', ''));
        $customers = collect();
        $cards     = collect();
        $chats     = collect();

        if (strlen($q) >= 2) {
            $customers = Customer::query()
                ->where('company_name', 'like', "%$q%")
                ->orWhere('client_name',  'like', "%$q%")
                ->orWhere('email',        'like', "%$q%")
                ->orWhere('plan_name',    'like', "%$q%")
                ->orWhere('tier',         'like', "%$q%")
                ->orWhereRaw("JSON_SEARCH(related_emails, 'one', ?) IS NOT NULL", ["%$q%"])
                ->limit(10)->get();

            $cards = Card::with('customer')
                ->where('contact_reason',    'like', "%$q%")
                ->orWhere('ombudsman_agent', 'like', "%$q%")
                ->orWhere('reason_details',  'like', "%$q%")
                ->orWhere('applied_solution','like', "%$q%")
                ->orWhere('responsible_team','like', "%$q%")
                ->orWhere('ticket_origin',   'like', "%$q%")
                ->orWhere(fn ($query) => is_numeric($q) ? $query->where('id', $q) : null)
                ->limit(10)->get();

            $chats = Chat::with('card.customer')
                ->where('id', 'like', "%$q%")
                ->limit(10)->get();
        }

        return view('dashboard', compact(
            'totalCustomers', 'activeCustomers', 'newThisMonth',
            'totalCards', 'openCards', 'retainedCards', 'churnCards', 'closedCards', 'retentionRate',
            'totalMrr',
            'activeProducts', 'talk2Products', 'hostProducts', 'totalAttendants',
            'recentCards', 'recentChanges',
            'q', 'customers', 'cards', 'chats'
        ));
    }
}
