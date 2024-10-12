<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Package;
use App\Models\PackageCategory;
use App\Models\UserPackage;
use Illuminate\Http\Request;

class PackageController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $user_packages = auth()->user()->packages()->with('package')->get();

        return view('packages.index', compact('user_packages'));
    }

    public function list()
    {
        $categories = PackageCategory::with('packages')->get();

        return view('packages.list', compact('categories'));
    }

    /**
     * Display the specified resource.
     */
    public function show(Request $request, Package $package)
    {
        // 检测用户是否有这个分类下的任意一个 package
        $user = $request->user(); // 获取当前用户
        $hasAccess = $user->hasSameCategoryPackage($package);

        if ($hasAccess) {
            return back()->withErrors(['package' => '你已经有该分类下的一个套餐，请在套餐页面续费或升级']);
        }

        return view('packages.buy', compact('package'));
    }

    public function renewPage(Request $request, UserPackage $userPackage)
    {
        $userPackage->load('package');
        if ($userPackage->user_id !== $request->user()->id) {
            abort(403);
        }
        return view('packages.renew', compact('userPackage'));
    }


}
