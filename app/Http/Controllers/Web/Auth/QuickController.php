<?php

namespace App\Http\Controllers\Web\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use App\Support\MultiUserSupport;
use Illuminate\Http\Request;

class QuickController extends Controller
{
    public function __construct(
        protected MultiUserSupport $multiUser,
        protected User $user
    ) {
        $this->multiUser = new MultiUserSupport;
    }

    public function quickLogin(Request $request, string $token)
    {
        $user = $this->user->getLoginToken($token);
        if (! $user) {
            return redirect()->to(RouteServiceProvider::HOME)->with('error', '登录请求的 Token 不存在或已过期。');
        }

        $this->multiUser->add($user);
        $this->multiUser->switch($user);

        return redirect()->to(RouteServiceProvider::HOME);
    }
}
