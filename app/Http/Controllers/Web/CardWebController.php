<?php

namespace App\Http\Controllers\Web;

use App\Events\CardCreated;
use App\Events\CardFinished;
use App\Events\CardUpdated;
use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\CardComment;
use App\Models\Chat;
use App\Models\Customer;
use App\Models\KanbanColumn;

class CardWebController extends Controller
{
    private function statuses(): array
    {
        return KanbanColumn::orderBy('order')->pluck('name')->toArray();
    }

    public function show(Card $card)
    {
        $card->load('customer', 'product', 'chats');
        $comments = $card->comments()->orderBy('created_at')->get();

        return view('cards.show', [
            'card'     => $card,
            'comments' => $comments,
            'statuses' => $this->statuses(),
        ]);
    }

    public function create()
    {
        $customers = Customer::orderBy('company_name')->get(['id', 'company_name', 'client_name']);
        $agents    = Card::whereNotNull('ombudsman_agent')->distinct()->orderBy('ombudsman_agent')->pluck('ombudsman_agent')->values();
        $origins   = Card::whereNotNull('ticket_origin')->distinct()->orderBy('ticket_origin')->pluck('ticket_origin')
                         ->merge(['RA', 'Email', 'Telefone', 'WhatsApp', 'Chat', 'Presencial'])->unique()->sort()->values();
        $teams     = Card::whereNotNull('responsible_team')->distinct()->orderBy('responsible_team')->pluck('responsible_team')
                         ->merge(['CS', 'Sucesso do Cliente', 'Comercial', 'Suporte', 'Produto', 'Financeiro'])->unique()->sort()->values();

        return view('cards.create', compact('customers', 'agents', 'origins', 'teams') + ['statuses' => $this->statuses()]);
    }

    public function store()
    {
        $data = request()->validate([
            'customer_id'      => 'required|exists:customers,id',
            'product_id'       => 'nullable|exists:products,id',
            'status'           => 'required|in:' . implode(',', $this->statuses()),
            'started_at'       => 'required|date',
            'contact_reason'   => 'nullable|string|max:255',
            'reason_details'   => 'nullable|string',
            'ombudsman_agent'  => 'nullable|string|max:100',
            'ticket_origin'    => 'nullable|string|max:100',
            'responsible_team' => 'nullable|string|max:100',
        ]);

        $card = Card::create($data);
        event(new CardCreated($card));
        return redirect()->route('cards.show', $card)->with('success', 'Card criado com sucesso.');
    }

    public function update(Card $card)
    {
        $wasFinished = $card->isFinished();

        $data = request()->validate([
            'status'           => 'sometimes|in:' . implode(',', $this->statuses()),
            'ombudsman_agent'  => 'nullable|string|max:100',
            'contact_reason'   => 'nullable|string|max:255',
            'reason_details'   => 'nullable|string',
            'responsible_team' => 'nullable|string|max:100',
            'applied_solution' => 'nullable|string',
            'ticket_origin'    => 'nullable|string|max:100',
            'ra_claim_link'    => 'nullable|string|max:500',
            'rating'           => 'nullable|integer|min:1|max:5',
            'finished_at'      => 'nullable|date',
        ]);

        if (isset($data['status']) && in_array($data['status'], ['Retido', 'Churn']) && !$card->finished_at) {
            $data['finished_at'] = $data['finished_at'] ?? now();
        }

        $card->update($data);
        $card->refresh();

        if (!$wasFinished && $card->isFinished()) {
            event(new CardFinished($card));
        } else {
            event(new CardUpdated($card));
        }

        return redirect()->route('cards.show', $card)->with('success', 'Salvo.');
    }

    public function updateStatus(Card $card)
    {
        $wasFinished = $card->isFinished();
        $status = request()->validate(['status' => 'required|in:' . implode(',', $this->statuses())])['status'];

        $card->update([
            'status'      => $status,
            'finished_at' => in_array($status, ['Retido', 'Churn']) ? ($card->finished_at ?? now()) : null,
        ]);
        $card->refresh();

        if (!$wasFinished && $card->isFinished()) {
            event(new CardFinished($card));
        } else {
            event(new CardUpdated($card));
        }

        return redirect()->route('cards.show', $card)->with('success', 'Etapa atualizada.');
    }

    public function moveStatus(Card $card)
    {
        $status = request()->input('status');

        if (!in_array($status, $this->statuses())) {
            return response()->json(['ok' => false, 'error' => 'Status inválido'], 422);
        }

        $wasFinished = $card->isFinished();

        $card->update([
            'status'      => $status,
            'finished_at' => in_array($status, ['Retido', 'Churn']) ? ($card->finished_at ?? now()) : null,
        ]);
        $card->refresh();

        if (!$wasFinished && $card->isFinished()) {
            event(new CardFinished($card));
        } else {
            event(new CardUpdated($card));
        }

        return response()->json(['ok' => true, 'status' => $status]);
    }

    public function storeChat(Card $card)
    {
        $data = request()->validate([
            'id'         => 'required|string|max:255|unique:chats,id',
            'started_at' => 'required|date',
        ]);

        $card->chats()->create($data);
        return redirect()->route('cards.show', $card)->with('success', 'Chat adicionado.');
    }

    public function closeChat(Card $card, Chat $chat)
    {
        abort_if($chat->ombudsman_card_id !== $card->id, 404);
        $chat->update(['closed_at' => now()]);
        return redirect()->route('cards.show', $card)->with('success', 'Chat encerrado.');
    }

    public function storeComment(Card $card)
    {
        $data = request()->validate([
            'author'  => 'nullable|string|max:100',
            'content' => 'required|string|max:2000',
        ]);

        $card->comments()->create($data);
        return redirect()->route('cards.show', $card)->with('success', 'Comentário adicionado.');
    }

    public function destroyComment(Card $card, CardComment $comment)
    {
        abort_if($comment->card_id !== $card->id, 404);
        $comment->delete();
        return redirect()->route('cards.show', $card)->with('success', 'Comentário removido.');
    }
}
