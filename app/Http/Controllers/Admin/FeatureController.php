<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Subscription\HasPeriodicity;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use LucasDotVin\Soulbscription\Models\Feature;

class FeatureController extends Controller
{
    use HasPeriodicity;

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $features = new Feature();

        if ($request->has('trashed')) {
            $features = $features->onlyTrashed();
        }

        $features = $features->paginate(100);

        return view('admin.features.index', compact('features'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.features.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $f = $this->validateFeature($request);
        $feature = Feature::create($f);

        return redirect()->route('admin.features.edit', $feature);
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
    public function edit(Feature $feature)
    {
        return view('admin.features.edit', compact('feature'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Feature $feature)
    {
        $f = $this->validateFeature($request);

        $feature->update($f);

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Feature $feature)
    {
        // 如果有计划正在使用，则禁止删除
        if ($feature->plans()->count() > 0) {
            return back()->withErrors('该特征正在被计划使用，禁止删除。');
        }

        if ($feature->tickets()->count() > 0) {
            return back()->withErrors('该特征正在被 Tickets 使用，禁止删除。');
        }

        $feature->delete();

        return redirect()->route('admin.features.index');
    }

    public function restore($feature_id)
    {
        Feature::withTrashed()->where('id', $feature_id)->restore();

        return redirect()->route('admin.features.index')->with('success', '已恢复删除。');
    }

    private function validateFeature(Request $request): array
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'consumable' => 'sometimes|boolean',
            'postpaid' => 'sometimes|boolean',
            'quota' => 'nullable|integer|min:0',
            'periodicity' => 'nullable|integer|min:0',
            'periodicity_type' => 'required|in:none,day,week,month,year',
        ]);


        $f = [
            'consumable' => $request->boolean('consumable'),
            'postpaid' => $request->boolean('postpaid'),
            'name' => $request->input('name'),
        ];

        if ($request->input('periodicity_type') != 'none') {
            $f['periodicity'] = $request->input('periodicity');

            if ($request->input('periodicity') < 1) {
                $f['periodicity'] = 1;
            }

            $f['periodicity_type'] = $this->matchType($request->input('periodicity_type'));
        }

        return $f;
    }
}
