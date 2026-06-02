<?php

namespace App\Http\Controllers\Api;

use App\Events\CardCreated;
use App\Events\CardFinished;
use App\Events\CardUpdated;
use App\Http\Controllers\Controller;
use App\Models\Card;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $cards = Card::query()
            ->when($request->get('customer_id'), fn ($q, $id) => $q->where('customer_id', $id))
            ->when($request->get('status'), fn ($q, $s) => $q->where('status', $s))
            ->when($request->get('ombudsman_agent'), fn ($q, $a) => $q->where('ombudsman_agent', $a))
            ->with(['customer', 'product'])
            ->orderByDesc('created_at')
            ->paginate($request->get('per_page', 20));

        return response()->json($cards);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(Card::with(['customer', 'product', 'chats'])->findOrFail($id));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'customer_id'       => 'required|integer|exists:customers,id',
            'product_id'        => 'nullable|integer|exists:products,id',
            'status'            => 'nullable|string|max:50',
            'started_at'        => 'required|date',
            'ticket_origin'     => 'nullable|string|max:100',
            'ombudsman_agent'   => 'nullable|string|max:100',
            'ra_claim_link'     => 'nullable|url|max:500',
            'contact_reason'    => 'nullable|string|max:255',
            'reason_details'    => 'nullable|string',
            'responsible_team'  => 'nullable|string|max:100',
            'is_sector_recurrent' => 'nullable|boolean',
        ]);

        $card = Card::create($data);

        event(new CardCreated($card));

        return response()->json($card, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $card = Card::findOrFail($id);
        $wasFinished = $card->isFinished();

        $data = $request->validate([
            'product_id'                      => 'nullable|integer|exists:products,id',
            'status'                          => 'nullable|string|max:50',
            'finished_at'                     => 'nullable|date',
            'ticket_origin'                   => 'nullable|string|max:100',
            'ombudsman_agent'                 => 'nullable|string|max:100',
            'ra_claim_link'                   => 'nullable|url|max:500',
            'rating'                          => 'nullable|integer|min:1|max:5',
            'first_response_hours'            => 'nullable|numeric|min:0',
            'ra_public_response_hours'        => 'nullable|numeric|min:0',
            'usage_time_post_ombudsman_hours'  => 'nullable|numeric|min:0',
            'contact_reason'                  => 'nullable|string|max:255',
            'reason_details'                  => 'nullable|string',
            'responsible_team'                => 'nullable|string|max:100',
            'applied_solution'                => 'nullable|string',
            'is_sector_recurrent'             => 'nullable|boolean',
        ]);

        $card->update($data);
        $card->refresh();

        if (!$wasFinished && $card->isFinished()) {
            event(new CardFinished($card));
        } else {
            event(new CardUpdated($card));
        }

        return response()->json($card);
    }

    public function destroy(int $id): JsonResponse
    {
        Card::findOrFail($id)->delete();

        return response()->json(null, 204);
    }
}
