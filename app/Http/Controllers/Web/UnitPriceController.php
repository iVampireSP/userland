<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\UnitPrice;

class UnitPriceController extends Controller
{
    public function index()
    {
        $unitPrice = UnitPrice::all();

        return view('unit_price.index', compact('unitPrice'));
    }
}
