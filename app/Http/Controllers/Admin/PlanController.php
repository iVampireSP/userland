<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\Subscription\HasPeriodicity;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use LucasDotVin\Soulbscription\Models\Plan;

class PlanController extends Controller
{
    use HasPeriodicity;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $plans = new Plan();

        if ($request->has('trashed')) {
            $plans = $plans->onlyTrashed();
        }

        $plans = $plans->paginate(100);

        return view('admin.plans.index', compact('plans'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.plans.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $p = $this->validatePlan($request);

        $plan = Plan::create($p);

        return redirect()->route('admin.plans.edit', $plan);
    }

    /**
     * Display the specified resource.
     */
    public function show(Plan $plan)
    {
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Plan $plan)
    {
        return view('admin.plans.edit', compact('plan'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Plan $plan)
    {
        $f = $this->validatePlan($request);

        $plan->update($f);

        return back();
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Plan $plan)
    {
        $plan->delete();

        return redirect()->route('admin.plans.index');
    }

    private function validatePlan(Request $request): array
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'grace_days' => 'required|integer|min:0',
            'periodicity' => 'nullable|integer|min:0',
            'periodicity_type' => 'required|in:none,day,week,month,year',
        ]);

        $f = [
            'name' => $request->input('name'),
            'grace_days' => $request->input('grace_days', '0'),
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

    public function restore($plan_id)
    {
        Plan::withTrashed()->where('id', $plan_id)->restore();

        return redirect()->route('admin.plans.index')->with('success', '已恢复删除。');
    }

}
