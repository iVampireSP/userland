<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;

class PermissionController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $permissions = Permission::paginate(100);

        return view('admin.permissions.index', compact('permissions'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $guards = array_keys(config('auth.guards'));

        return view('admin.permissions.create', compact('guards'));
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

        // reject if exists
        $exists = Permission::where('name', $request->input('name'))
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

        Permission::create([
            'name' => $request->input('name'),
            'guard_name' => $guard,
        ]);

        return redirect()->route('admin.permissions.index')->with('success', '权限创建成功');
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
    public function edit(Permission $permission)
    {
        $guards = array_keys(config('auth.guards'));

        return view('admin.permissions.edit', compact('permission', 'guards'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Permission $permission)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'guard' => 'nullable|string|max:255',
        ]);

        if ($permission->name != $request->input('name')) {
            $exists = Permission::where('name', $request->input('name'))
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

        $permission->update([
            'name' => $request->input('name'),
            'guard_name' => $guard,
        ]);

        return redirect()->route('admin.permissions.index')->with('success', '权限编辑成功');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Permission $permission)
    {
        $permission->delete();

        return redirect()->route('admin.permissions.index')->with('success', '权限删除成功');
    }
}
