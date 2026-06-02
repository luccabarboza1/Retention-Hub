<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CustomerLookupController extends Controller
{
    public function lookup(Request $request)
    {
        $request->validate(['email' => 'required|email']);

        $url = config('app.customer_lookup_url');

        if (!$url) {
            return response()->json([
                'error' => 'Integração de lookup não configurada (CUSTOMER_LOOKUP_URL).'
            ], 503);
        }

        try {
            $response = Http::timeout(30)
                ->acceptJson()
                ->post($url, ['email' => $request->email]);

            if ($response->successful()) {
                $data = $response->json();

                // Garante que o email informado está presente no retorno
                $data['email'] = $data['email'] ?? $request->email;

                return response()->json($data);
            }

            return response()->json([
                'error' => 'O n8n retornou um erro (' . $response->status() . ').'
            ], 422);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            return response()->json([
                'error' => 'Não foi possível conectar ao n8n. Verifique se o workflow está ativo.'
            ], 503);
        }
    }
}
