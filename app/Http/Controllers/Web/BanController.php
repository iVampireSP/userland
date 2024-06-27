<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;

class BanController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $bans = auth('web')->user()->bans()->latest()->paginate(20);

        return view('bans.index', compact('bans'));
    }
}
