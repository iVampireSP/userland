<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

class DiscoveryController extends Controller
{
    public function __invoke(Request $request)
    {
        $response = [
            'issuer' => url('/'),
            'authorization_endpoint' => route('passport.authorizations.authorize'),
            'token_endpoint' => route('passport.token'),
        ];
        
        if (Route::has('openid.jwks')) {
            $response['jwks_uri'] = route('openid.jwks');
        }
        
        if (Route::has('openid.userinfo')) {
            $response['userinfo_endpoint'] = route('openid.userinfo');
        }

        $response['response_types_supported'] = [
            'code',
            'token',
            'id_token',
            'code token',
            'code id_token',
            'token id_token',
            'code token id_token',
            'none',
        ];

        $response['subject_types_supported'] = [
            'public',
        ];

        $response['id_token_signing_alg_values_supported'] = [
            'RS256',
        ];

        $response['scopes_supported'] = config('openid.passport.tokens_can');

        $response['token_endpoint_auth_methods_supported'] = [
            'client_secret_basic',
            'client_secret_post',
        ];

        return response()->json($response, 200, [], JSON_PRETTY_PRINT);
    }
}
