<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $roles = Role::paginate(100);

        return view('admin.roles.index', compact('roles'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $guards = array_keys(config('auth.guards'));

        return view('admin.roles.create', compact('guards'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'guard' => 'nullable|string|max:255',
        ]);

        $exists = Role::where('name', $request->input('name'))
            ->exists();
        if ($exists) {
            return redirect()->back()->withErrors(['name' => '权限已存在']);
        }

        $guard = $request->input('guard');
        if ($guard == '') {
            $guard = '';
        } else {
            if (! in_array($guard, array_keys(config('auth.guards')))) {
                return redirect()->back()->withErrors(['guard' => '无效的 guard']);
            }
        }

        Role::create([
            'name' => $request->input('name'),
            'guard_name' => $guard,
        ]);

        return redirect()->route('admin.roles.index')->with('success', '权限创建成功');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Role $role)
    {
        $guards = array_keys(config('auth.guards'));

        return view('admin.roles.edit', compact('role', 'guards'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Role $role)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'guard' => 'nullable|string|max:255',
        ]);

        if ($role->name != $request->input('name')) {
            $exists = Role::where('name', $request->input('name'))
                ->exists();
            if ($exists) {
                return redirect()->back()->withErrors(['name' => '权限已存在']);
            }
        }

        $guard = $request->input('guard');
        if ($guard == '') {
            $guard = '';
        } else {
            if (! in_array($guard, array_keys(config('auth.guards')))) {
                return redirect()->back()->withErrors(['guard' => '无效的 guard']);
            }
        }

        $role->update([
            'name' => $request->input('name'),
            'guard_name' => $guard,
        ]);

        return redirect()->route('admin.roles.index')->with('success', '权限编辑成功');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Role $role)
    {
        $role->delete();

        return redirect()->route('admin.roles.index')->with('success', '权限删除成功');
    }

    public function permissions(Role $role)
    {
        $permissions = Permission::where('guard_name', $role->guard_name)->paginate(100);

        return view('admin.roles.permissions', compact('role', 'permissions'));
    }

    public function togglePermission(Request $request, Role $role, Permission $permission)
    {
        $has = $role->hasPermissionTo($permission);

        if ($has) {
            $role->revokePermissionTo($permission);
        } else {
            $role->givePermissionTo($permission);
        }

        //        return response()->json(['attached' => $attached]);
        return redirect()->back();
    }
}
