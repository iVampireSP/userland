<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ManifestController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        return response()->json([
            'name' => config('app.display_name'),
            'short_name' => config('app.name'),
            'description' => config('app.display_name'),
            'start_url' => '/',
            'display' => 'standalone',
            'icons' => [
                [
                    'src' => '/images/logo.png',
                    'sizes' => '192x192',
                    'type' => 'image/png',
                ],
            ],
        ]);
    }
}
