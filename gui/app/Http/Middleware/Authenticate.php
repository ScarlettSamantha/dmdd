<?php
declare(strict_types=1);

namespace Scarlett\DMDD\GUI\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Support\Facades\Auth;

class Authenticate extends Middleware
{
    public function handle($request, \Closure $next, ...$guards)
    {
        // Check if the user is logged in via Laravel's auth system
        if (!Auth::check()) {
            // Redirect to login if the user isn't authenticated
            return $this->unauthenticatedResponse($request);
        }

        // Allow the request to proceed
        return $next($request);
    }

    /**
     * Handle unauthenticated response.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response|\Illuminate\Http\JsonResponse
     */
    protected function unauthenticatedResponse($request)
    {
        if (!$request->expectsJson()) {
            return redirect()->route('login');
        }

        return response()->json(['message' => 'Unauthorized'], 401);
    }
}
