<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp\Client;

class VerifyToken
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Extract token from Authorization header
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $token = substr($authHeader, 7);

        // Verify the token with Google's API
        $http = new Client();
        try {
            $response = $http->get('https://www.googleapis.com/oauth2/v3/tokeninfo', [
                'query' => ['id_token' => $token],
            ]);

            $googleUser = json_decode($response->getBody(), true);

            // Check for required fields
            if (!isset($googleUser['email'])) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Optionally, match the email with your database
            $user = \App\Models\User::where('email', $googleUser['email'])->first();
            if (!$user) {
                return response()->json(['error' => 'Unauthorized'], 401);
            }

            // Add the authenticated user to the request
            $request->merge(['user' => $user]);

        } catch (\Exception $e) {
            return response()->json(['error' => 'Invalid Google token'], 401);
        }

        return $next($request);
    }
}
