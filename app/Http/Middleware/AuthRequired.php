<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AuthRequired
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {   
        if($request->has('is_authenticated') && $request->get('is_authenticated') && $request->has('user') && $request->get('user')){
            return $next($request);
        }
        $result = [
            'success'   => 0,
            'msg'       => 'Token has expired, Please Login again!',
            'data'      => null
        ];
        return response()->json($result, 401);
    }
}
