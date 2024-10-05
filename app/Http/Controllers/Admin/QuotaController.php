<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Quota;
use Illuminate\Http\Request;

class QuotaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $quotas = Quota::paginate(100);

        return view('admin.quotas.index', compact('quotas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.quotas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'unit' => 'required|string|unique:quotas,unit|max:255',
            'description' => 'required|string|max:255',
        ]);

        $quota = Quota::create([
            'unit' => $request->input('unit'),
            'description' => $request->input('description'),
        ]);

        return redirect()->route('admin.quotas.index')->with('success', '配额创建成功');
    }

    /**
     * Display the specified resource.
     */
    public function show(Quota $quota)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Quota $quota)
    {
        return view('admin.quotas.edit', compact('quota'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Quota $quota)
    {
        $request->validate([
            'unit' => 'required|string|unique:quotas,unit,'.$quota->id.'|max:255',
            'description' => 'required|string|max:255',
        ]);
        $quota->update([
            'unit' => $request->input('unit'),
            'description' => $request->input('description'),
        ]);

        return redirect()->route('admin.quotas.index')->with('success', '配额更新成功');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Quota $quota)
    {
        // 检测配额是否被使用
        if ($quota->users()->count() > 0) {
            return redirect()->route('admin.quotas.index')->with('error', '该配额正在被使用，无法删除');
        }

        $quota->delete();

        return redirect()->route('admin.quotas.index')->with('success', '配额删除成功');
    }
}
