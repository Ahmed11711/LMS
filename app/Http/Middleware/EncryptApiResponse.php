<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EncryptApiResponse
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $content = $response->getContent();
        $key     = config('app.api_secret');
        $iv      = substr(hash('sha256', $key), 0, 16);

        $encrypted = openssl_encrypt(
            $content,
            'AES-256-CBC',
            $key,
            0,
            $iv
        );

        return response()->json([
            'data' => base64_encode($encrypted)
        ], $response->getStatusCode());
    }
}
