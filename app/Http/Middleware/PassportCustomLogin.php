<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PassportCustomLogin
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->getStatusCode() == 302 && auth('web')->guest()) {
            return redirect()->route('login.custom', [
                'client' => $request->input('client_id'),
            ]);
        }

        return $response;
    }
}
