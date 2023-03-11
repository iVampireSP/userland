<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;
use RuntimeException;

class TokenController extends Controller
{
    public function index(): Factory|\Illuminate\Foundation\Application|View|Application
    {
        $tokens = auth('web')->user()->tokens()->paginate(10);

        return view('token.index', compact('tokens'));
    }

    public function create(): Factory|\Illuminate\Foundation\Application|View|Application
    {
        $scopes = Passport::scopes();

        return view('token.create', compact('scopes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'scopes' => 'required|array',
        ]);

        try {
            $token = $request->user('web')->createToken($request->input('name'), $request->input('scopes'))->accessToken;
        } catch (RuntimeException $e) {
            return redirect()->route('tokens.create')->with('error', '无法创建令牌。可能是您没有预先创建个人访问令牌客户端。');
        }

        return redirect()->route('tokens.index')->with('token', $token);
    }

    public function destroy(Request $request, $id): RedirectResponse
    {
        $request->user('web')->tokens()->where('id', $id)->delete();

        return redirect()->route('tokens.index')->with('success', '令牌已删除。');
    }
}
