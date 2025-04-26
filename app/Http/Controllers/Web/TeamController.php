<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Team;
use App\Models\TeamInvitation;
use App\Models\User;
use App\Notifications\TeamInvitation as TeamInvitationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Notification;

class TeamController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $teams = $user->teams;

        return view('teams.index', compact('teams'));
    }

    public function create()
    {
        return view('teams.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $team = Team::create([
            'name' => $validated['name'],
            'description' => $validated['description'],
            'owner_id' => Auth::id(),
        ]);

        $team->members()->attach(Auth::id(), ['role' => 'owner']);
        Auth::user()->update(['current_team_id' => $team->id]);

        return redirect()->route('teams.show', $team)->with('success', '团队创建成功！');
    }

    public function show(Team $team)
    {
        $this->authorize('view', $team);
        $members = $team->members()->get();

        return view('teams.show', compact('team', 'members'));
    }

    public function edit(Team $team)
    {
        $this->authorize('update', $team);

        return view('teams.edit', compact('team'));
    }

    public function update(Request $request, Team $team)
    {
        $this->authorize('update', $team);
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
        ]);

        $team->update($validated);

        return redirect()->route('teams.show', $team)->with('success', '团队信息已更新！');
    }

    public function destroy(Team $team)
    {
        $this->authorize('delete', $team);

        // 开始事务处理，确保数据一致性
        \DB::beginTransaction();

        try {
            // 1. 清除所有将此团队设为当前团队的用户的 current_team_id
            User::where('current_team_id', $team->id)->update(['current_team_id' => null]);

            // 2. 删除所有相关的团队邀请
            TeamInvitation::where('team_id', $team->id)->delete();

            // 3. 删除团队成员关联
            // 注意：这里使用 detach() 而不是直接删除数据库记录
            $team->members()->detach();

            // 4. 最后删除团队本身
            $team->delete();

            // 提交事务
            \DB::commit();

            return redirect()->route('teams.index')->with('success', '团队及所有相关数据已删除！');

        } catch (\Exception $e) {
            // 出现异常时回滚事务
            \DB::rollBack();

            return redirect()->route('teams.show', $team)
                ->with('error', '删除团队失败：'.$e->getMessage());
        }
    }

    public function invite(Request $request, Team $team)
    {
        $this->authorize('invite', $team);
        $validated = $request->validate([
            'email' => 'required|email|exists:users,email',
        ]);

        $user = User::where('email', $validated['email'])->first();

        if ($team->members()->where('user_id', $user->id)->exists()) {
            return back()->with('error', '无法邀请，因为该用户已经是团队成员！');
        }

        // 只检查是否有待处理的邀请
        $pendingInvitation = TeamInvitation::where('team_id', $team->id)
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->first();

        if ($pendingInvitation) {
            return back()->with('error', '已经向该用户发送过邀请！');
        }

        // 删除任何旧的邀请记录（已接受或已拒绝）
        TeamInvitation::where('team_id', $team->id)
            ->where('user_id', $user->id)
            ->delete();

        TeamInvitation::create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        Notification::send($user, new TeamInvitationNotification($team));

        return back()->with('success', '邀请已发送！');
    }

    public function invitations()
    {
        $invitations = TeamInvitation::where('user_id', Auth::id())
            ->where('status', 'pending')
            ->with('team.owner')
            ->get();

        return view('teams.invitations', compact('invitations'));
    }

    public function acceptInvitation(TeamInvitation $invitation)
    {
        if ($invitation->user_id !== Auth::id()) {
            abort(403);
        }

        if (! $invitation->isPending()) {
            return back()->with('error', '邀请已失效！');
        }

        $team = $invitation->team;
        $team->members()->attach(Auth::id(), ['role' => 'member']);
        $invitation->delete(); // Delete the invitation after accepting

        return redirect()->route('teams.show', $team)
            ->with('success', '已加入团队！');
    }

    public function rejectInvitation(TeamInvitation $invitation)
    {
        if ($invitation->user_id !== Auth::id()) {
            abort(403);
        }

        if (! $invitation->isPending()) {
            return back()->with('error', '邀请已失效！');
        }

        $invitation->delete(); // Delete the invitation after rejecting

        return back()->with('success', '已拒绝邀请！');
    }

    public function switchTeam(Team $team)
    {
        $this->authorize('view', $team);
        Auth::user()->update(['current_team_id' => $team->id]);

        return back()->with('success', '已切换到团队：'.$team->name);
    }

    public function updateMemberRole(Request $request, Team $team, User $user)
    {
        $this->authorize('updateRole', $team);
        $validated = $request->validate([
            'role' => 'required|in:member,admin',
        ]);

        if ($team->owner_id === $user->id) {
            return back()->with('error', '不能修改团队所有者的角色！');
        }

        $team->members()->updateExistingPivot($user->id, ['role' => $validated['role']]);

        return back()->with('success', '成员角色已更新！');
    }

    public function removeMember(Team $team, User $user)
    {
        $this->authorize('removeMember', $team);
        if ($team->owner_id === $user->id) {
            return back()->with('error', '不能移除团队所有者！');
        }

        $team->members()->detach($user->id);
        if ($user->current_team_id === $team->id) {
            $user->update(['current_team_id' => null]);
        }

        return back()->with('success', '成员已移除！');
    }

    /**
     * 允许用户离开团队
     *
     * @param  Team  $team  要离开的团队
     * @return \Illuminate\Http\RedirectResponse
     */
    public function leaveTeam(Team $team)
    {
        $user = Auth::user();
        $this->authorize('leaveTeam', $team);

        // 检查用户是否确实是团队成员
        if (! $team->members()->where('user_id', $user->id)->exists()) {
            return redirect()->route('teams.index')->with('error', '您不是该团队的成员！');
        }

        // 如果用户是团队所有者，不允许离开
        if ($team->owner_id === $user->id) {
            return back()->with('error', '团队所有者不能离开团队，请先转让所有权或删除团队！');
        }

        // 移除用户与团队的关联
        $team->members()->detach($user->id);

        // 如果用户的当前团队是要离开的团队，则清除当前团队设置
        if ($user->current_team_id === $team->id) {
            $user->update(['current_team_id' => null]);
        }

        return redirect()->route('teams.index')->with('success', '您已成功离开团队：'.$team->name);
    }
}
