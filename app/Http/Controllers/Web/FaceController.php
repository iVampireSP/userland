<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FaceController extends Controller
{
    public function index() {
        $face = auth()->user()->faces()->validate()->first();

        dd($face);
    }
}
