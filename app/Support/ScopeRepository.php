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
        $config_scopes = config('openid.passport.tokens_can');
        $scopes = [];

        foreach ($config_scopes as $key => $value) {
            $scopes[$key] = ['description' => $value];
        }

        if (array_key_exists($identifier, $scopes) === false) {
            return;
        }

        $scope = new ScopeEntity();
        $scope->setIdentifier($identifier);

        return $scope;
    }
}
