<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PackagePermissionController extends Controller
{
    public function roles(Package $package)
    {
        $roles = Role::paginate(100);

        return view('admin.packages.roles', compact('roles', 'package'));
    }

    public function permissions(Package $package)
    {
        $permissions = Permission::paginate(100);

        return view('admin.packages.permissions', compact('permissions', 'package'));
    }

    public function toggleRole(Package $package, Role $role)
    {
        $has = $package->hasRole($role);

        if ($has) {
            $package->removeRole($role);
        } else {
            $package->assignRole($role);
        }

        return redirect()->back();
    }

    public function togglePermission(Package $package, Permission $permission)
    {
        $has = $package->hasPermissionTo($permission);

        if ($has) {
            $package->revokePermissionTo($permission);
        } else {
            $package->givePermissionTo($permission);
        }

        return redirect()->back();
    }
}
