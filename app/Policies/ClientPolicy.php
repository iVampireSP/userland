<?php

namespace App\Policies;

use App\Models\Client;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClientPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return auth('admin')->check();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Client $client): bool
    {
        // 调试信息：检查用户 ID 和客户端的用户 ID
        // dd($user->id, $client->user_id);

        return auth('admin')->check() || $user->id === $client->user_id;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        // 只要已登录都可以创建(无论什么 guard)
        return auth()->check();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Client $client): bool
    {
        return auth('admin')->check() || $user->id === $client->user_id;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Client $client): bool
    {
        return auth('admin')->check() || $user->id === $client->user_id;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, Client $client): bool
    {
        return auth('admin')->check();
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, Client $client): bool
    {
        return auth('admin')->check();
    }

    public function managePush(User $user, Client $client): bool
    {
        return auth('admin')->check() || $user->id === $client->user_id;
    }
}
