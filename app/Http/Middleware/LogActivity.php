<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

use App\Models\ActivityLogs;

use Illuminate\Support\Facades\Route;


class LogActivity
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Process the request
        $response = $next($request);

        // Get current route and controller info
        $routeAction = Route::getCurrentRoute()->getAction();
        $controller = isset($routeAction['controller']) ? class_basename($routeAction['controller']) : null;
        $action = isset($routeAction['controller']) ? 
            substr(strrchr($routeAction['controller'], "@"), 1) : null;

                    // Get response data based on response type
        $responseData = null;
        if ($response instanceof \Illuminate\Http\JsonResponse) {
            $responseData = $response->getData(true); // true to get as array
        } elseif ($response instanceof \Illuminate\Http\Response) {
            // Try to decode JSON content if possible
            try {
                $content = $response->getContent();
                $responseData = json_decode($content, true);
            } catch (\Exception $e) {
                $responseData = null;
            }
        }

        $user = $request->get('user')?? null;
        // Create activity log
        ActivityLogs::create([
            'user_id' => $user? $user->id: null, // Will be null for guests
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'method' => $request->method(),
            'path' => $request->path(),
            'controller' => $controller,
            'action' => $action,
            'request_data' => $this->filterSensitiveData($request->all()),
            'response_data' => $this->filterSensitiveData($responseData),
            'response_status' => $response->getStatusCode()
        ]);

        return $response;
    }

    private function filterSensitiveData($data)
    {
        if (is_array($data)) {
            unset($data['password']);
            unset($data['password_confirmation']);
        }
        return $data;
    }

}
