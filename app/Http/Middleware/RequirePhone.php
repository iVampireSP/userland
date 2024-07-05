<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RequirePhone
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $key = 'auth.phone_confirmed_at';

        // 检查是否有这个 session
        if (! session()->has($key)) {
            // 检查用户是否绑定手机号
            if (! $request->user('web')->isPhoneVerified()) {
                return redirect()->route('phone.create')->with('error', '接下来的操作需要验证手机号，但是您没有绑定。请绑定后再进行下一步操作。');
            }

            return redirect()->route('phone.validate');
        }

        return $next($request);
    }
}
