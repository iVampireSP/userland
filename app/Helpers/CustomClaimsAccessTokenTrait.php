<?php

namespace App\Helpers;

use App\Models\User;
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

        $user = User::findOrFail($this->getUserIdentifier());

        $r = $this->jwtConfiguration->builder()
            ->permittedFor($this->getClient()->getIdentifier())
            ->identifiedBy($this->getIdentifier())
            ->issuedAt(new DateTimeImmutable())
            ->canOnlyBeUsedAfter(new DateTimeImmutable())
            ->expiresAt($this->getExpiryDateTime())
            ->relatedTo($user->uuid);

        $r->withHeader('kid', config('openid.kid'));
        $r->withHeader('typ', 'JWT');

        // 绝对不要使用 Access Token 做认证。Access Token 本身不能标识用户是否已经认证。
        // Access Token 中只包含了用户 id，在 sub 字段。在你开发的应用中，应该将 Access Token 视为一个随机字符串，不要试图从中解析信息。

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
