<?php

namespace App\Http\Middleware;

use App\Models\PersonalAccessTokens;
use App\Models\User;
use Carbon\Carbon;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Routing\Middleware\ThrottleRequests;
use Illuminate\Cache\RateLimiting\Limit;
use GuzzleHttp\Client;

class VerifyToken
{
    protected $throttle;

    public function __construct(ThrottleRequests $throttle)
    {
        $this->throttle = $throttle;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {

        // Create a response closure for throttle
        // $response = function ($request) use ($next) {
        //     return $next($request);
        // };

        // Extract token from Authorization header
        $authHeader = $request->header('Authorization');

        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            $request->merge(['is_authenticated' => false]);
            // $this->throttle->handle($request, $response, '30', '1');
            // $this->throttle->handle($request, $response, ...explode(',', config('app.throttle.guest')));
            return $this->throttle->handle($request, $next, ...explode(',', config('app.throttle.guest')));

        }else{    
            $token = substr($authHeader, 7);
    
            $isAuthenticated = $this->validateToken($token);
            if($isAuthenticated && $isAuthenticated['success']){
                $request->merge(['user' => $isAuthenticated['user']]);
                $request->merge(['is_authenticated' => true]);

                $request->setUserResolver(function() use ($isAuthenticated){
                    return $isAuthenticated['user'];
                });

                // $this->throttle->handle($request, $response, '60', '1');
                // Apply authenticated throttle from config
                return $this->throttle->handle($request, $next, ...explode(',', config('app.throttle.authenticated')));

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
        // return $next($request);
        return $this->throttle->handle($request, $next, ...explode(',', config('app.throttle.guest')));
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
