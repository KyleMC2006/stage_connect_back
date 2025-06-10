<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware; // <-- Import the Middleware class

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php', // Make sure your API routes are included
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // --- API Middleware Configuration ---
        // This is crucial for Laravel Sanctum (or Passport) for SPA/token authentication.
        // It ensures that requests coming from your frontend (even on a different subdomain)
        // can be authenticated using Sanctum's cookie-based session (for SPA)
        // or just by checking for the 'Authorization: Bearer' header.
        $middleware->api(prepend: [
            \Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class,
        ]);

        // You might have other global API middleware here, for example:
        // $middleware->api(append: [
        //     // \App\Http\Middleware\ForceJsonRequestHeader::class, // Example if you want to ensure JSON requests for all API endpoints
        // ]);

        // --- Middleware Aliases (if you use them directly in routes) ---
        // Laravel Sanctum typically registers 'auth:sanctum' by default,
        // but if you have custom middleware or want to be explicit,
        // you would define aliases here.
        $middleware->alias([
            'auth:sanctum' => \Laravel\Sanctum\Http\Middleware\AuthenticateWithApiToken::class,
            // 'abilities' => \Laravel\Sanctum\Http\Middleware\CheckAbilities::class,
            // 'ability' => \Laravel\Sanctum\Http\Middleware\CheckForAnyAbility::class,
            // 'my_custom_middleware' => \App\Http\Middleware\MyCustomMiddleware::class,
        ]);

        // --- Other Middleware Groups (Web is typically default) ---
        // If you had web routes, their middleware would be configured here too.
        $middleware->web(append: [
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            // ... other default web middleware
        ]);

        // --- Global Middleware (applies to ALL routes) ---
        // Less common for API-only backends, but you could add them here:
        // $middleware->append(
        //     \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        // );

        // If you wanted to apply specific Sanctum middleware for SPA mode
        // Note: statefulApi() is a convenience method for common Sanctum SPA setup
        // If you call $middleware->statefulApi(); it's usually enough.
        // $middleware->statefulApi(); // This method internally adds EnsureFrontendRequestsAreStateful

    })
    ->withExceptions(function (Exceptions $exceptions) {
        // This section is for custom exception handling.
        // For instance, if you want to return JSON for authentication exceptions:
        // $exceptions->render(function (AuthenticationException $e, Request $request) {
        //     if ($request->is('api/*')) {
        //         return response()->json(['message' => 'Unauthenticated.'], 401);
        //     }
        // });
    })
    ->create();