<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Cache\RateLimiter;

use Illuminate\Support\Facades\Cache;



class ConcurrentRequestsThrottle
{
    // RateLimiter instance for handling rate limiting
    protected $limiter;

    // Constructor to inject RateLimiter dependency
    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    // Middleware handle method which processes incoming requests
    public function handle(Request $request, Closure $next)
    {
        // Create a unique key for the request based on its URL
        $key = 'concurrent_requests:' . $request->url();

        // Attempt to acquire a lock for the given key
        if (!Cache::add($key, true, 10)) { // 10 seconds lock time
            // If the lock is not acquired, return a 'Too Many Requests' response
            return $this->buildResponse();
        }

        // Process the request further down the middleware stack
        try {
            return $response = $next($request);
        } finally {
            // Release the lock after the request is processed
            Cache::forget($key);
        }

        // Return the response
        // return $response;
    }

    // Method to build the 'Too Many Requests' response
    protected function buildResponse()
    {
        return response()->json([
            'message' => 'Too Many Requests'
        ], 429); // Return a JSON response with status code 429 (Too Many Requests)
    }
}
