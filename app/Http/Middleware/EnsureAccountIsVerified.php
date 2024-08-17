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
     */
    public static function redirectTo(string $route): string
    {
        return static::class.':'.$route;
    }

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, ?string $redirectToRoute = null): Response|RedirectResponse|null
    {
        $user = $request->user('web');

        if ($user || (! $user->isPhoneVerified() || ! $user->isEmailVerified())) {
            if ($request->expectsJson()) {
                abort(403, '你需要先验证账户。');
            } else {
                return Redirect::guest(URL::route($redirectToRoute ?: 'verification.notice'));
            }
        }

        return $next($request);
    }
}
