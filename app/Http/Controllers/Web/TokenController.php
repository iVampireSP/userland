<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Laravel\Passport\Passport;
use RuntimeException;

class TokenController extends Controller
{
    public function index(): View
    {
        $tokens = auth('web')->user()->tokens()->with('client')->paginate(100);

        return view('tokens.index', compact('tokens'));
    }

    public function create(): View
    {
        $scopes = Passport::scopes();

        return view('tokens.create', compact('scopes'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'scopes' => 'nullable|array',
        ]);

        $scopes = $request->input('scopes', []);

        try {
            $token = $request->user('web')->createToken($request->input('name'), $scopes)->accessToken;
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

    public function destroy_all(Request $request): RedirectResponse
    {
        $request->user('web')->tokens()->delete();

        return redirect()->route('tokens.index')->with('success', '所有令牌已删除。');
    }

    public function display_scopes(): View
    {
        $scopes = Passport::scopes();

        return view('tokens.scopes', compact('scopes'));
    }
}
