<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\URL;

class EnsureAccountIsVerified
{
    /**
     * Specify the redirect route for the middleware.
     *
     * @param string $route
     * @return string
     */
    public static function redirectTo(string $route): string
    {
        return static::class . ':' . $route;
    }

    /**
     * Handle an incoming request.
     *
     * @param Request $request
     * @param Closure $next
     * @param string|null $redirectToRoute
     * @return Response|RedirectResponse|null
     */
    public function handle(Request $request, Closure $next, string $redirectToRoute = null): Response|RedirectResponse|null
    {
        $user = $request->user('web');

        if (!$user || (!$user->isPhoneVerified() || !$user->isEmailVerified())) {
            if ($request->expectsJson()) {
                abort(403, 'Your account is not verified.');
            } else {
                Redirect::guest(URL::route($redirectToRoute ?: 'verification.notice'));
            }
        }

        return $next($request);
    }
}
