<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ValidateGatewayToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->header('Authorization');

        if (!$token) {
            return response()->json(['error' => 'Unauthorized: Missing token'], 401);
        }
        $gatewayValidationUrl = env('GATEWAY_VALIDATION_URL', 'http://127.0.0.1:8001/api/validate-token');
        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])->get($gatewayValidationUrl);

            if ($response->failed()) {
                if ($response->status() === 401) {
                    return response()->json(['error' => 'Unauthorized: Invalid token'], 401);
                }
                Log::error('Gateway token validation failed: ' . $response->body());
                return response()->json(['error' => 'Gateway validation error'], 500);
            }

            return $next($request);
        } catch (\Exception $e) {
            Log::error('Gateway token validation exception: ' . $e->getMessage());
            return response()->json(['error' => 'Internal server error'], 500);
        }

    }
}
