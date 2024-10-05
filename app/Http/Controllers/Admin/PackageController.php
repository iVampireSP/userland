<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\Quota;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $packages = Package::with('category')->paginate(100);

        return view('admin.packages.index', compact('packages'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $categories = PackageCategory::all();

        return view('admin.packages.create', compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'content' => 'required|string|max:255',
            'name' => 'required|unique:packages,name|string|max:255',
            'category_id' => 'required|integer|exists:package_categories,id',
        ]);

        Package::create([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'content' => $request->input('content'),
            'name' => $request->input('name'),
            'category_id' => $request->input('category_id'),
            'enable_quota' => $request->has('enable_quota'),
            'hidden' => true,
            'max_active_count' => 0,
        ]);


        return redirect()->route('admin.packages.index');
    }

    /**
     * Display the specified resource.
     */
    public function show(Package $package)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Package $package)
    {
        $categories = PackageCategory::all();

        return view('admin.packages.edit', compact('package', 'categories'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Package $package)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string|max:255',
            'content' => 'required|string|max:255',
            'name' => 'required|string|max:255|unique:packages,name,' . $package->id,
            'category_id' => 'required|integer|exists:package_categories,id',
            'max_active_count' => 'nullable|integer',

//            'enabled_day' => 'required|boolean',
//            'enabled_week' => 'required|boolean',
//            'enabled_month' => 'required|boolean',
//            'enabled_year' => 'required|boolean',
//
//            'price_day' => 'nullable|numeric',
//            'price_week' => 'nullable|numeric',
//            'price_month' => 'nullable|numeric',
//            'price_year' => 'nullable|numeric',
//            'price_forever' => 'nullable|numeric',

        ]);

        // hidden is checkbox


        $package->update([
            'title' => $request->input('title'),
            'description' => $request->input('description'),
            'content' => $request->input('content'),
            'name' => $request->input('name'),
            'category_id' => $request->input('category_id'),
            'enable_day' => $request->has('enable_day'),
            'enable_week' => $request->has('enable_week'),
            'enable_month' => $request->has('enable_month'),
            'enable_year' => $request->has('enable_year'),
            'enable_forever' => $request->has('enable_forever'),
            'enable_quota' => $request->has('enable_quota'),

            'price_day' => $request->input('price_day', 0),
            'price_week' => $request->input('price_week', 0),
            'price_month' => $request->input('price_month', 0),
            'price_year' => $request->input('price_year', 0),
            'price_forever' => $request->input('price_forever', 0),

            'hidden' => $request->has('hidden'),
            'max_active_count' => $request->input('max_active_count', 0),
        ]);

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Package $package)
    {
        if ($package->users()->count() == 0) {
            $package->delete();
        }
    }

//


}
