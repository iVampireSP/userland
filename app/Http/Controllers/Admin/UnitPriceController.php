<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnitPrice;
use Illuminate\Http\Request;

class UnitPriceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $unit_prices = UnitPrice::paginate(100);

        return view('admin.units.index', compact('unit_prices'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.units.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'unit' => 'required|string|unique:unit_prices,unit|max:255',
            'name' => 'required|string|max:255',
            'price_per_unit' => 'required|numeric',
        ]);

        UnitPrice::create([
            'unit' => $request->input('unit'),
            'name' => $request->input('name'),
            'price_per_unit' => $request->input('price_per_unit'),
        ]);

        return redirect()->route('admin.unit_prices.index')->with('success', '计价单位创建成功');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(UnitPrice $unitPrice)
    {
        return view('admin.units.edit', compact('unitPrice'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, UnitPrice $unitPrice)
    {
        $request->validate([
            'unit' => 'required|string|unique:unit_prices,unit,'.$unitPrice->id.'|max:255',
            'name' => 'required|string|max:255',
            'price_per_unit' => 'required|numeric',
        ]);

        $unitPrice->update([
            'unit' => $request->input('unit'),
            'name' => $request->input('name'),
            'price_per_unit' => $request->input('price_per_unit'),
        ]);

        return redirect()->route('admin.unit_prices.index')->with('success', '单位更新成功');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(UnitPrice $unitPrice)
    {
        // 检测配额是否被使用
        if ($unitPrice->users()->count() > 0) {
            return redirect()->route('admin.unit_prices.index')->with('error', '该单位正在被使用，无法删除');
        }

        $unitPrice->delete();

        return redirect()->route('admin.unit_prices.index')->with('success', '单位删除成功');
    }
}
