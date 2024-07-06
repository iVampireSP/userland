<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\Request;

class WeChatController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user('web');

        if ($user->wechat_open_id) {
            return view('wechat.index');
        }

        return $this->show_bind($user);
    }

    public function show_bind(User $user)
    {
        $minutes = 10;

        $token = $user->createLoginToken(
            expired_at: now()->addMinutes($minutes),
            length: 7,
            prefix: 'wechat:bind',
            avoid_confusion: true
        );

        return view('wechat.bind', [
            'token' => 'b'.$token,
            'minutes' => $minutes,
        ]);
    }

    public function unbind(Request $request)
    {
        $user = $request->user('web');

        $user->wechat_open_id = null;
        $user->save();

        return redirect()->to(RouteServiceProvider::HOME)->with('success', '您已成功解绑微信。');
    }
}
