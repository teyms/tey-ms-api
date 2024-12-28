<?php

namespace App\Http\Middleware;

use App\Models\PersonalAccessTokens;
use App\Models\User;
use Carbon\Carbon;
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
            $request->merge(['is_authenticated' => false]);

        }else{    
            $token = substr($authHeader, 7);
    
            $isAuthenticated = $this->validateToken($token);
            if($isAuthenticated && $isAuthenticated['success']){
                $request->merge(['user' => $isAuthenticated['user']]);
                $request->merge(['is_authenticated' => true]);
            }else{
                $request->merge(['user' => null]);
                $request->merge(['is_authenticated' => false]);   
                
                $result = [
                    'success'   => $isAuthenticated['success'],
                    'msg'       => $isAuthenticated['msg'],
                    'expired'   => $isAuthenticated['expired'],
                    'user'      => null
                ];
                return response()->json($result, 401);
            }
            
        }
        return $next($request);
    }

    private function validateToken($authToken){
        try {
            $personalAccessToken = PersonalAccessTokens::Where('token', $authToken)->latest()->first();

            if($personalAccessToken){
                $test = Carbon::parse($personalAccessToken->expires_at);
                if($personalAccessToken->expires_at && Carbon::parse($personalAccessToken->expires_at)->isPast()){
                    $result = [
                        'success'   => 0,
                        'msg'       => 'Token has expired, Please Login again!',
                        'expired'   => true,
                        'user'      => null,
                    ];
                    return $result;
                }

                $user = User::where('id', $personalAccessToken->tokenable_id)->first();
                if ($user) {
                    $result = [
                        'success'   => 1,
                        'msg'       => 'Token has expired, Please Login again!',
                        'expired'   => true,                        
                        'user'      => $user
                    ];
                    return $result;
                }
            }

        } catch (\Exception $e) {
            $result = [
                'success'   => 0,
                'msg'       => 'Authentication Failed',
                'expired'   => false,                
                'user'      => null
            ];
            return $result;
        }
        $result = [
            'success'   => 0,
            'msg'       => 'Authentication Failed',
            'expired'   => false,            
            'user'      => null
        ];
        return $result;
    }
}
