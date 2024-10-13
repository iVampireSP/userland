<?php

namespace App\Http\Controllers\Application;

use App\Http\Controllers\Controller;
use App\Models\UnitPrice;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BalanceController extends Controller
{
    public function reduce(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0|max:10000',
            'reason' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::find($request->input('user_id'));

        $user->reduce($request->input('amount'), $request->input('reason'));

        return $this->noContent();
    }

    public function add(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0|max:10000',
            'reason' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
        ]);

        $user = User::find($request->input('user_id'));

        $user->charge($request->input('amount'), $request->input('reason'));

        return $this->noContent();
    }

    public function unit_add(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0|max:10000',
            'reason' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'unit' => 'required|string|max:255',
        ]);

        // 检测 unit 是否存在
        $unit = UnitPrice::whereUnit($request->input('unit'))->firstOrFail();
        $user = User::find($request->input('user_id'));

        $amount = $unit->calculatePrice($request->input('amount'));

        $user->charge($amount, $request->input('reason'));

        return $this->noContent();
    }

    public function unit_reduce(Request $request): JsonResponse
    {
        $request->validate([
            'amount' => 'required|numeric|min:0|max:10000',
            'reason' => 'required|string|max:255',
            'user_id' => 'required|exists:users,id',
            'unit' => 'required|string|max:255',
        ]);

        $unit = UnitPrice::whereUnit($request->input('unit'))->firstOrFail();
        $user = User::find($request->input('user_id'));

        $amount = $unit->calculatePrice($request->input('amount'));

        $user->reduce($amount, $request->input('reason'));

        return $this->noContent();
    }
}
