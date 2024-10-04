<?php

namespace App\Support\OAuth;

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

    protected array $scopes;

    public function __construct()
    {
        $this->config = Configuration::forSymmetricSigner(
            app(config('openid.signer')),
            InMemory::plainText(config('passport.private_key'))
        );
    }

    // Id Token 仅适用于认证场景。例如，有一个应用使用了谷歌登录，然后同步用户的日历信息，谷歌会返回 Id Token 给这个应用，Id Token 中包含用户的基本信息（用户名、头像等）。应用可以解析 Id Token 然后利用其中的信息，展示用户名和头像。
    // 不推荐使用 Id Token 来进行 API 的访问鉴权。
    public function getBuilder(AccessTokenEntityInterface $accessToken): Builder
    {
        $dateTimeImmutableObject = new DateTimeImmutable;

        $user = User::findOrFail($accessToken->getUserIdentifier());

        $r = $this->config
            ->builder()
            ->permittedFor($accessToken->getClient()->getIdentifier())
            // id token 里面不应该有 jti, 否则他可以访问账户系统
            // ->identifiedBy($accessToken->getIdentifier()) // jti
            ->issuedBy(url('/'))
            ->issuedAt($dateTimeImmutableObject)
            ->expiresAt($dateTimeImmutableObject->add(new DateInterval('PT1H')))
            ->relatedTo($user->id)
            ->withClaim('scopes', $this->getScopes($accessToken))
            ->withHeader('kid', config('openid.kid'))
            ->withHeader('typ', 'id_token');

        $claims = $user->getClaims($this->scopes);
        foreach ($claims as $key => $value) {
            $r = $r->withClaim($key, $value);
        }

        return $r;
    }

    public function getExtraParams(AccessTokenEntityInterface $accessToken): array
    {
        // 如果有 openid scope
        if (! in_array('openid', $this->getScopes($accessToken))) {
            return [];
        }

        $token = $this->getBuilder($accessToken)->getToken(
            $this->config->signer(),
            $this->config->signingKey(),
        );

        return [
            'id_token' => $token->toString(),
        ];
    }

    protected function getScopes(AccessTokenEntityInterface $accessToken): array
    {
        if (empty($this->scopes)) {
            $token_scopes = $accessToken->getScopes();

            $scopes = [];

            foreach ($token_scopes as $scope) {
                $scopes[] = $scope->getIdentifier();
            }

            $this->scopes = $scopes;

            return $scopes;
        }

        return $this->scopes;
    }
}
