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
            'openid' => ['description' => '启用 OpenID 支持（将会获取 id_token）'],
            'profile' => ['description' => '获取你的基本信息（包括昵称、头像）'],
            'email' => ['description' => '获取你的邮件地址'],
            'phone' => ['description' => '获取你的手机号'],
            'address' => ['description' => '获取你的通讯地址'],
            'realname' => '获取你的实名信息（包括姓名、身份证号）',
        ];

        if (array_key_exists($identifier, $scopes) === false) {
            return;
        }

        $scope = new ScopeEntity();
        $scope->setIdentifier($identifier);
        return $scope;
    }
}
