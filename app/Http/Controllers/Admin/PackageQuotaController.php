<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\Quota;
use Illuminate\Http\Request;

class PackageQuotaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Package $package)
    {
        $package->load('quotas.quota');
        $quotas = Quota::paginate(100);

        return view('admin.packages.quotas', compact('package', 'quotas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Package $package, Quota $quota)
    {
        if ($package->quotas()->where('quota_id', $quota->id)->exists()) {
            return back()->withErrors('此计量单位已经绑定过了。');
        } else {
            $request->validate([
                'max_amount' => 'required|integer|min:0',
                'reset_rule' => 'required|in:none,day,week,month,half_year,year',
            ]);

            $package->quotas()->create([
                'quota_id' => $quota->id,
                'package_id' => $package->id,
                'max_amount' => $request->input('max_amount', 0),
                'reset_rule' => $request->input('reset_rule'),
            ]);

        }

        return redirect()->route('admin.packages.quotas.index', $package);
    }

    public function destroy(Package $package, Quota $quota)
    {
        $package->quotas()->where('quota_id', $quota->id)->delete();

        return back();
    }
}
