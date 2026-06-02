<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Chat;
use App\Models\Customer;

class SearchController extends Controller
{
    public function __invoke()
    {
        $q = trim(request('q', ''));

        if (strlen($q) < 2) {
            return view('search', ['q' => $q, 'customers' => collect(), 'cards' => collect(), 'chats' => collect()]);
        }

        $customers = Customer::query()
            ->where(function ($query) use ($q) {
                $query->where('company_name', 'like', "%$q%")
                      ->orWhere('client_name',  'like', "%$q%")
                      ->orWhere('email',         'like', "%$q%")
                      ->orWhere('plan_name',     'like', "%$q%")
                      ->orWhere('tier',          'like', "%$q%")
                      ->orWhereRaw("JSON_SEARCH(related_emails, 'one', ?) IS NOT NULL", ["%$q%"]);
            })
            ->limit(10)->get();

        $cards = Card::with('customer')
            ->where(function ($query) use ($q) {
                $query->where('contact_reason',    'like', "%$q%")
                      ->orWhere('ombudsman_agent', 'like', "%$q%")
                      ->orWhere('reason_details',  'like', "%$q%")
                      ->orWhere('applied_solution','like', "%$q%")
                      ->orWhere('responsible_team','like', "%$q%")
                      ->orWhere('ticket_origin',   'like', "%$q%");
                if (is_numeric($q)) $query->orWhere('id', $q);
            })
            ->limit(10)->get();

        $chats = Chat::with('card.customer')
            ->where('id', 'like', "%$q%")
            ->limit(10)->get();

        return view('search', compact('q', 'customers', 'cards', 'chats'));
    }
}
