<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\UserStatus;
use App\Support\OAuth\IdTokenResponse;
use DateTimeImmutable;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function realName(): JsonResponse
    {
        $user = auth('api')->user();

        return $this->success([
            'real_name' => $user->real_name,
            'id_card' => $user->id_card,
            'real_name_verified_at' => $user->real_name_verified_at,
        ]);
    }

    public function user(Request $request)
    {
        $user = $request->user('api');

        $scopes = $this->getScopes($user);
        $claims = $this->getScopeClaims($user, $scopes);

        return $this->success($claims);
    }

    public function status(Request $request)
    {
        if ($request->isMethod('get')) {
            return $this->success(
                $request->user('api')->status()->first() ?? UserStatus::DEFAULT
            );
        }

        $request->validate([
            'text' => 'nullable|string',
        ]);

        // 如果用户有了状态，就更新，否则就创建
        $user = $request->user('api');

        $status = $user->status()->updateOrCreate([
            'user_id' => $user->id,
        ], [
            'text' => $request->input('text'),
        ]);

        return $this->success($status);
    }

    public function token(Request $request)
    {
        $user = $request->user('api');

        if (!$user->tokenCan('token')) {
            return $this->forbidden('No permission for token scope.');
        }

        $idToken = new IdTokenResponse;

        $dateTimeImmutableObject = new DateTimeImmutable;

        $scopes = $this->getScopes($user);

        $token = $idToken->issueForUser($dateTimeImmutableObject, $request->user(), $scopes);
        $config = $idToken->getConfig();

        $idTokenString = $token->getToken($config->signer(), $config->signingKey())->toString();

        return $this->success([
            'token' => $idTokenString,
        ]);
    }

    private function getScopes(User $user): array
    {
        $all_scopes = config('openid.passport.tokens_can');

        $scopes = [];

        foreach ($all_scopes as $scope_name => $scope_description) {
            if ($user->tokenCan($scope_name)) {
                $scopes[] = $scope_name;
            }
        }

        return $scopes;
    }

    private function getScopeClaims(User $user, array $scopes): array
    {

        return $user->getClaims($scopes);
    }
}
