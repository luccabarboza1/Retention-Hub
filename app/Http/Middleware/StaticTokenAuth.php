<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class StaticTokenAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = config('app.api_access_token');

        if (empty($expected) || $request->bearerToken() !== $expected) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        return $next($request);
    }
}
