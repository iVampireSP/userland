<?php

namespace App\Helpers;

use DateTimeImmutable;
use Lcobucci\JWT\Token;
use League\OAuth2\Server\Entities\Traits\AccessTokenTrait;

trait CustomClaimsAccessTokenTrait
{
    use AccessTokenTrait;

    /**
     * Generate a JWT from the access token
     */
    private function convertToJWT(): Token
    {
        $this->initJwtConfiguration();

        $r = $this->jwtConfiguration->builder()
            ->permittedFor($this->getClient()->getIdentifier())
            ->identifiedBy($this->getIdentifier())
            ->issuedAt(new DateTimeImmutable())
            ->canOnlyBeUsedAfter(new DateTimeImmutable())
            ->expiresAt($this->getExpiryDateTime())
            ->relatedTo((string) $this->getUserIdentifier());

        $r->withHeader('kid', config('openid.kid'));
        $r->withHeader('typ', 'JWT');

        $r->withClaim('scopes', $this->getScopes());

        return $r->getToken($this->jwtConfiguration->signer(), $this->jwtConfiguration->signingKey());
    }

    /**
     * Generate a string representation from the access token
     */
    public function __toString()
    {
        return $this->convertToJWT()->toString();
    }
}
