<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    public function roles(User $user)
    {
        $roles = Role::paginate(100);

        return view('user.roles', compact('roles', 'user'));
    }

    public function permissions(User $user)
    {
        $permissions = Permission::paginate(100);

        return view('user.permissions', compact('permissions', 'user'));
    }


}
