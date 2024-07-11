<?php

namespace App\Support;

use App\Models\User;
use DateInterval;
use DateTimeImmutable;
use Lcobucci\JWT\Builder;
use Lcobucci\JWT\Configuration;
use Lcobucci\JWT\Signer\Key\InMemory;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\ResponseTypes\BearerTokenResponse;

class IdTokenResponse extends BearerTokenResponse
{
    protected Configuration $config;

    public function __construct()
    {
        $this->config = Configuration::forSymmetricSigner(
            app(config('openid.signer')),
            InMemory::plainText(config('passport.private_key'))
        );

    }

    protected function getBuilder(AccessTokenEntityInterface $accessToken): Builder
    {
        $dateTimeImmutableObject = new DateTimeImmutable();

        $user = User::findOrFail($accessToken->getUserIdentifier());
        $token_scopes = $accessToken->getScopes();

        $scopes = [];

        foreach ($token_scopes as $scope) {
            $scopes[] = $scope->getIdentifier();
        }

        $r = $this->config
            ->builder()
            ->permittedFor($user->id)
            ->issuedBy(url('/'))
            ->issuedAt($dateTimeImmutableObject)
            ->expiresAt($dateTimeImmutableObject->add(new DateInterval('PT1H')))
            ->relatedTo($user->id);

        $r->withHeader('kid', config('openid.kid'));
        $r->withHeader('typ', 'id_token');

        $claims = $user->getClaims($scopes);
        foreach ($claims as $key => $value) {
            $r->withClaim($key, $value);
        }

        return $r;
    }

    protected function getExtraParams(AccessTokenEntityInterface $accessToken): array
    {
        $token = $this->getBuilder($accessToken)->getToken(
            $this->config->signer(),
            $this->config->signingKey(),
        );

        return [
            'id_token' => $token->toString(),
        ];
    }
}
