<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateGatewayToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('Authorization');

        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Validar el token con el API Gateway
        $response = Http::withHeaders([
            'Authorization' => $token
        ])->get('http://127.0.0.1:8001/api/validate-token'); // URL del Gateway

        if ($response->failed()) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        return $next($request);
    }
    
}
