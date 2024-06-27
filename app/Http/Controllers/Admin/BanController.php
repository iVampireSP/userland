<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ban;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class BanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request, User $user)
    {
        $bans = $user->bans()->with('client');

        if ($request->filled('code')) {
            $bans = $bans->where('code', 'like', '%'.$request->input('code').'%');
        }

        $bans = $bans->latest()->paginate(20);

        return view('admin.bans.index', compact('bans', 'user'));
    }

    /**
     * 编辑封禁
     */
    public function edit(User $user, Ban $ban)
    {
        return view('admin.bans.edit', compact('ban', 'user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user, Ban $ban)
    {
        $request->validate([
            'code' => 'nullable|string',
            'reason' => 'nullable|string',
            'expires_at' => 'nullable|date',
            'pardoned' => 'nullable|boolean',
        ]);

        $expired_at = $request->expires_at;

        if ($request->filled('expires_at')) {
            $expired_at = Carbon::parse($request->input('expires_at'))->toDateTimeString();
        }

        // 如果 is_expired
        if ($request->filled('is_expired')) {
            $expired_at = now();
        }

        // 更新经过验证的
        $ban->update([
            'code' => $request->input('code'),
            'reason' => $request->input('reason'),
            'expired_at' => $expired_at,
            'pardoned' => $request->input('pardoned'),
        ]);

        return back()->with('success', '已更新封禁。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user, Ban $ban)
    {
        $ban->delete();

        return redirect()->route('admin.bans.index', $user)->with('success', '已删除封禁。');
    }
}
