<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response;

class Recaptcha
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 如果不是 post
        if ($request->method() !== 'POST') {
            return $next($request);
        }

        // 如果请求中不包含
        if (! $request->filled('g-recaptcha-response')) {
            return back()->with('error', '请先完成验证码验证');
        }

        $http = Http::asForm()->post('https://www.recaptcha.net/recaptcha/api/siteverify', [
            'secret' => config('recaptcha.secret_key'),
            'response' => $request->input('g-recaptcha-response'),
            'remoteip' => $request->getClientIp(),
        ])->json();

        if (! $http['success']) {
            return back()->with('error', '验证码验证失败');
        }

        return $next($request);
    }
}
