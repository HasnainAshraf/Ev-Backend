<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        // For API requests, return null to let the middleware handle JSON responses
        if ($request->expectsJson()) {
            return null;
        }
        
        // For web requests, redirect to login (if you have web routes)
        return route('login');
    }
}
