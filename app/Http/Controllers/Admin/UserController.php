<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\UserDeleteJob;
use App\Models\User;
use App\Support\Auth\MultiUserSupport;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use LucasDotVin\Soulbscription\Models\Plan;

class UserController extends Controller
{
    protected MultiUserSupport $multiUserSupport;

    public function __construct()
    {
        $this->multiUserSupport = app(MultiUserSupport::class);
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        $users = new User;

        if ($request->filled('id')) {
            $users = $users->where('id', $request->input('id'));
        }

        if ($request->filled('name')) {
            $users = $users->where('name', 'like', '%'.$request->input('name').'%');
        }

        if ($request->filled('email')) {
            $users = $users->where('email', 'like', '%'.$request->input('email').'%');
        }

        if ($request->filled('real_name')) {
            $users = $users->where('real_name', 'like', '%'.$request->input('real_name').'%');
        }

        if ($request->has('banned_at')) {
            $users = $users->whereNotNull('banned_at');
        }

        if ($request->has('real_name_verified_at')) {
            $users = $users->whereNotNull('real_name_verified_at');
        }

        $users = $users->paginate(50)->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    /**
     * Display the specified resource.
     */
    public function show(User $user): RedirectResponse
    {
        $this->multiUserSupport->add($user);
        $this->multiUserSupport->switch($user);

        return back()->with('success', '您已切换到用户 '.$user->name.' 的身份。');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user): View
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'id_card' => 'nullable|string|size:18',
            'password' => 'nullable|string|min:6',
        ]);

        if ($request->input('is_banned')) {
            $user->banned_at = Carbon::now();

            if ($request->filled('banned_reason')) {
                $user->banned_reason = $request->input('banned_reason');
            }
        } else {
            if ($user->banned_at) {
                $user->banned_at = null;
            }
        }

        if ($request->has('real_name')) {
            $user->real_name = $request->input('real_name');
        }

        if ($request->has('id_card')) {
            $user->id_card = $request->input('id_card');
        }

        if ($request->filled('password')) {
            $user->password = bcrypt($request->input('password'));
        }

        if ($user->isDirty()) {
            $user->save();
        }

        return back()->with('success', '已完成所有更改。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user): RedirectResponse
    {
        dispatch(new UserDeleteJob($user));

        return redirect()->route('admin.users.index')->with('success', '正在删除此用户。');
    }

    public function status(Request $request, User $user): RedirectResponse
    {
        $request->validate([
            'emoji' => 'nullable|string',
            'status' => 'nullable|string',
            'text' => 'nullable|string',
        ]);

        // 如果用户有了状态，就更新，否则就创建
        $user->status()->updateOrCreate([
            'user_id' => $user->id,
        ], [
            'emoji' => $request->input('emoji'),
            'status' => $request->input('status'),
            'text' => $request->input('text'),
        ]);

        return back()->with('success', '已更新用户状态。');
    }

    public function subscriptions(User $user): View
    {
        $user->load('subscription');
        //        $user->subscription->load('plan');
        //        $user->subscription->load('plan.features');

        return view('admin.users.subscriptions', compact('user'));
    }

    public function addSubscription(User $user, Plan $plan): RedirectResponse
    {
        $user->subscribeTo($plan);

        return back()->with('success', '已添加订阅。');
    }

    public function removeSubscription(User $user): RedirectResponse
    {
        $user->subscription()->delete();

        return back()->with('success', '已删除订阅。');
    }
}
