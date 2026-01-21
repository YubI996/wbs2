<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiKeyAuth
{
    /**
     * API keys yang valid (idealnya simpan di database/config)
     */
    protected array $validApiKeys = [
        'sk_live_wbs_superapps_2026', // SuperApps integration
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-Key');

        if (!$apiKey) {
            return response()->json([
                'success' => false,
                'message' => 'API key tidak ditemukan. Sertakan header X-API-Key.',
            ], 401);
        }

        // Cek dari config jika ada, fallback ke hardcoded
        $configKey = config('services.superapps.api_key');
        $validKeys = $configKey ? array_merge($this->validApiKeys, [$configKey]) : $this->validApiKeys;

        if (!in_array($apiKey, $validKeys)) {
            return response()->json([
                'success' => false,
                'message' => 'API key tidak valid.',
            ], 401);
        }

        return $next($request);
    }
}
