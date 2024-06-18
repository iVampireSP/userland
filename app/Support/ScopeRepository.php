<?php

namespace App\Support;

use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Entities\ScopeEntityInterface;
use League\OAuth2\Server\Repositories\ScopeRepositoryInterface;
use OpenIDConnect\Entities\ScopeEntity;

class ScopeRepository implements ScopeRepositoryInterface
{
    public function finalizeScopes(
        array $scopes,
        $grantType,
        ClientEntityInterface $clientEntity,
        $userIdentifier = null
    ) {
        return array_filter($scopes, function (ScopeEntityInterface $scope) {
            return $this->getScopeEntityByIdentifier($scope->getIdentifier());
        });
    }

    public function getScopeEntityByIdentifier($identifier)
    {
        $scopes = [
            'openid' => ['description' => 'Enable OpenID Connect'],
            'profile' => ['description' => 'Information about your profile'],
            'email' => ['description' => 'Information about your email address'],
            'phone' => ['description' => 'Information about your phone numbers'],
            'address' => ['description' => 'Information about your address'],
            'realname' => '获取用户的实名信息（包括姓名、身份证号）',
            'user' => '获取用户的基本信息',
            'login' => '允许生成快速登录链接',
        ];

        if (array_key_exists($identifier, $scopes) === false) {
            return;
        }

        $scope = new ScopeEntity();
        $scope->setIdentifier($identifier);
        return $scope;
    }
}
