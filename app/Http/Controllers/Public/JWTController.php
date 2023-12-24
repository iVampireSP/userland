<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\JWTRefreshToken;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class JWTController extends Controller
{
    public int $ttl = 7200;

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'attributes' => 'nullable|array|max:255',
            'callback_uri' => 'nullable|url',
        ]);

        if ($request->filled('callback_uri') && $request->hasHeader('referer')) {
            // 如果有 referer，检查是否和来源域名一致
            $referer = parse_url($request->header('referer'), PHP_URL_HOST);

            // callback uri 的域名
            $returnUrl = parse_url($request->input('callback_uri'), PHP_URL_HOST);

            if ($referer !== $returnUrl) {
                return $this->error('来源域名不匹配。');
            }
        }

        $token = Str::random(128);

        $data = [
            'meta' => [
                'description' => $request->input('description'),
                'token' => $token,
                'attributes' => $request->input('attributes'),
                'callback_uri' => $request->input('callback_uri'),
                'authorized' => false,
            ],
        ];

        Cache::put('auth_request:'.$token, $data, 120);

        $data['url'] = route('auth_request.show', $token);
        $data['validate'] = route('public.auth_request.show', $token);

        return $this->success($data);
    }

    public function show($token): JsonResponse
    {
        $data = Cache::get('auth_request:'.$token);

        if (empty($data)) {
            return $this->error('Token 不存在或已过期。');
        }

        $generated_data = Cache::get('auth_request:'.$token.'_data');
        if (! empty($generated_data)) {
            return $this->success($generated_data);
        }

        if (! isset($data['user'])) {
            $data['user'] = null;
            $data['jwt'] = null;
        } else {
            $data['meta']['authorized'] = true;

            $jwt_info = [
                'claims' => [
                    'user' => $data['user'],
                ],
            ];

            $user = User::find($data['user']['id']);

            if (isset($data['meta']['attributes']) && is_array($data['meta']['attributes'])) {
                $jwt_info['claims'] = array_merge($jwt_info['claims'], $data['meta']['attributes']);
            }

            // 安排 JWT
            $jwt_info['token'] = auth('jwt')->setTTL($this->ttl)->claims($jwt_info['claims'])->login($user);

            $jwt_info['refresh_token'] = $refreshToken = Str::random(128);

            JWTRefreshToken::create([
                'claims' => $jwt_info['claims'],
                'refresh_token' => $refreshToken,
                'user_id' => $user->id,
            ]);

            $data['expires_in'] = $this->ttl;

            $data['token'] = $jwt_info['token'];
            $data['refresh_token'] = $jwt_info['refresh_token'];

            Cache::put('auth_request:'.$token.'_data', $data, 120);
        }

        return $this->success($data);
    }

    public function refresh(Request $request)
    {
        $request->validate([
            'refresh_token' => 'string|max:128|required',
        ]);

        $refresh_token = JWTRefreshToken::where('refresh_token', $request->input('refresh_token'))->first();

        if (! $refresh_token) {
            return $this->notFound();
        }

        $token = auth('jwt')->setTTL($this->ttl)->claims($refresh_token['claims'])->login($refresh_token->user);

        $data['expires_in'] = $this->ttl;
        $data['token'] = $token;

        return $this->success($data);
    }

    public function jwks()
    {
        $publicKeyResource = openssl_pkey_get_public(config('jwt.keys.public'));
        $publicKeyDetails = openssl_pkey_get_details($publicKeyResource);

        // 提取模数(n)和指数(e)，并进行 Base64 编码
        $n = base64_encode($publicKeyDetails['rsa']['n']);
        $e = base64_encode($publicKeyDetails['rsa']['e']);

        // 对于URL安全的 Base64 编码，我们需要进行一些替代和修剪
        $n = str_replace(['+', '/', '='], ['-', '_', ''], $n);
        $e = str_replace(['+', '/', '='], ['-', '_', ''], $e);

        // 创建 JWKS 结构
        $jwks = [
            'keys' => [
                [
                    'kty' => 'RSA',
                    'use' => 'sig',
                    'alg' => 'RS256',
                    'kid' => 'account-server',
                    'n' => $n,
                    'e' => $e,
                ],
            ],
        ];

        return response()->json($jwks);
    }
}
