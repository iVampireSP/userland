<?php

namespace App\Policies;

use App\Models\Team;
use App\Models\User;

class TeamPolicy
{
    public function view(User $user, Team $team)
    {
        return $team->members()->where('user_id', $user->id)->exists();
    }

    public function update(User $user, Team $team)
    {
        return $team->members()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->exists();
    }

    public function delete(User $user, Team $team)
    {
        return $team->owner_id === $user->id;
    }

    public function invite(User $user, Team $team)
    {
        return $team->members()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->exists();
    }

    public function updateRole(User $user, Team $team)
    {
        return $team->owner_id === $user->id;
    }

    public function removeMember(User $user, Team $team)
    {
        return $team->members()
            ->where('user_id', $user->id)
            ->whereIn('role', ['owner', 'admin'])
            ->exists();
    }

    public function leaveTeam(User $user, Team $team)
    {
        // 任何团队成员都可以离开团队，除了团队所有者
        return $team->members()->where('user_id', $user->id)->exists() && $team->owner_id !== $user->id;
    }
}
