<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Card;
use App\Models\Chat;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ChatController extends Controller
{
    public function index(int $cardId): JsonResponse
    {
        $card = Card::findOrFail($cardId);

        return response()->json($card->chats()->orderByDesc('created_at')->get());
    }

    public function store(Request $request, int $cardId): JsonResponse
    {
        Card::findOrFail($cardId);

        $data = $request->validate([
            'id'         => 'required|string|max:255',
            'started_at' => 'nullable|date',
            'closed_at'  => 'nullable|date',
        ]);

        $chat = Chat::create([
            ...$data,
            'ombudsman_card_id' => $cardId,
        ]);

        return response()->json($chat, 201);
    }

    public function destroy(int $cardId, string $chatId): JsonResponse
    {
        $chat = Chat::where('ombudsman_card_id', $cardId)->findOrFail($chatId);
        $chat->delete();

        return response()->json(null, 204);
    }
}
