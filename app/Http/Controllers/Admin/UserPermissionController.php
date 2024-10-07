<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UserPermissionController extends Controller
{
    public function roles(User $user)
    {
        $roles = Role::paginate(100);

        return view('admin.users.roles', compact('roles', 'user'));
    }

    public function permissions(User $user)
    {
        $permissions = Permission::paginate(100);

        return view('admin.users.permissions', compact('permissions', 'user'));
    }

    public function toggleRole(User $user, Role $role)
    {
        $has = $user->hasRole($role);

        if ($has) {
            $user->removeRole($role);
        } else {
            $user->assignRole($role);
        }

        return redirect()->back();
    }

    public function togglePermission(User $user, Permission $permission)
    {
        $has = $user->hasPermissionTo($permission);

        if ($has) {
            $user->revokePermissionTo($permission);
        } else {
            $user->givePermissionTo($permission);
        }

        return redirect()->back();
    }
}
