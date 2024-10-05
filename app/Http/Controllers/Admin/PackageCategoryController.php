<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PackageCategory;
use Illuminate\Http\Request;

class PackageCategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $categories = PackageCategory::all();

        return view('admin.package_categories.index', compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.package_categories.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:package_categories,slug'
        ]);

        PackageCategory::create([
            'name' => $request->name,
            'slug' => $request->slug
        ]);

        return redirect()->route('admin.package_categories.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(PackageCategory $packageCategory)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PackageCategory $packageCategory)
    {
        return view('admin.package_categories.edit', compact('packageCategory'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PackageCategory $packageCategory)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'required|string|max:255|unique:package_categories,slug,'.$packageCategory->id
        ]);

        $packageCategory->update([
            'name' => $request->input('name'),
            'slug' => $request->input('slug')
        ]);

        return redirect()->route('admin.package_categories.index');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PackageCategory $packageCategory)
    {
        // 如果有 package
        if ($packageCategory->packages()->exists()) {
            return redirect()->route('admin.package_categories.index')->with('error', '该分类下有套餐，无法删除');
        }

        $packageCategory->delete();
        return redirect()->route('admin.package_categories.index');
    }
}
