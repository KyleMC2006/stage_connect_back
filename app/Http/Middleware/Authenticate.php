<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Auth\AuthenticationException;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
         // If the request expects a JSON response (which is always the case for your API frontend),
        // or if it's any request in an API-only context,
        // return null. This tells Laravel NOT to redirect.
        // Instead, Laravel's default exception handler will return a 401 Unauthorized response.
        if (!$request->expectsJson()) {
             // For API-only, you don't want any web redirect.
             // This covers cases where a browser might hit an API route directly.
             return null;
        }

        // For JSON requests (your primary use case), return null.
        return null;
    }

    /**
     * Handle an unauthenticated user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  array  $guards
     * @throws \Illuminate\Auth\AuthenticationException
     */
    protected function unauthenticated($request, array $guards)
    {
        if ($request->expectsJson()) {
            // For API requests, throw an AuthenticationException directly.
            // Laravel's Exception Handler will then convert this into a JSON 401 response.
            throw new AuthenticationException('Unauthenticated.', $guards);
        }

        // If for some reason a non-JSON request hits this and needs a redirect,
        // you might still try to redirect. However, for a pure API, this block
        // should ideally not be reached.
        parent::unauthenticated($request, $guards);
    }
}