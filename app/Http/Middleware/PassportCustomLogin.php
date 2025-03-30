<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
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

        if (auth('web')->check()) {
            if ($request->input('client_id') && $request->input('nonce')) {
                // set client id with user_id nonce
                $user_id = auth('web')->user()->id;

                $key = 'passport:client_id:'.$request->input('client_id').':nonce:'.$user_id;

                Cache::set($key, $request->input('nonce'), now()->addMinutes(10));
            }
        }

        if ($response->getStatusCode() == 302 && auth('web')->guest()) {
            return redirect()->route('login.custom', [
                'client' => $request->input('client_id'),
            ]);
        }

        return $response;
    }
}
