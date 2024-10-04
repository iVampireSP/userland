<?php

namespace App\Helpers\OAuth;

use App\Models\User;
use App\Support\OAuth\AccessTokenResponse;
use App\Support\OAuth\IdTokenResponse;
use DateTimeImmutable;
use Error;
use Exception;
use GuzzleHttp\Psr7\Response;
use Illuminate\Events\Dispatcher;
use Laravel\Passport\Bridge\AccessTokenRepository;
use Laravel\Passport\Bridge\Client;
use Laravel\Passport\Bridge\RefreshTokenRepository;
use Laravel\Passport\Bridge\Scope;
use Laravel\Passport\Passport;
use Laravel\Passport\TokenRepository;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\ResponseTypes\BearerTokenResponse;
use Psr\Http\Message\MessageInterface;
use Psr\Http\Message\ResponseInterface;
use TypeError;

class PassportToken
{
    /**
     * Generate a new unique identifier.
     *
     *
     * @throws OAuthServerException
     */
    public function generateUniqueIdentifier(int $length = 40): string
    {
        try {
            return bin2hex(random_bytes($length));
            // @codeCoverageIgnoreStart
        } catch (TypeError|Error) {
            throw OAuthServerException::serverError('An unexpected error has occurred');
        } catch (Exception) {
            // If you get this message, the CSPRNG failed hard.
            throw OAuthServerException::serverError('Could not generate a random string');
        }
        // @codeCoverageIgnoreEnd
    }

    /**
     * @throws UniqueTokenIdentifierConstraintViolationException
     * @throws Exception
     */
    public function issueRefreshToken(AccessTokenEntityInterface $accessToken)
    {
        $maxGenerationAttempts = 10;
        $refreshTokenRepository = app(RefreshTokenRepository::class);

        $now = new DateTimeImmutable;

        $refreshToken = $refreshTokenRepository->getNewRefreshToken();
        $accessToken->setExpiryDateTime($now->add(Passport::refreshTokensExpireIn()));
        $refreshToken->setAccessToken($accessToken);

        while ($maxGenerationAttempts-- > 0) {
            try {
                $refreshToken->setIdentifier($this->generateUniqueIdentifier());
            } catch (OAuthServerException) {
                abort(500, 'Could not generate a unique access token identifier.');
            }
            try {
                $refreshTokenRepository->persistNewRefreshToken($refreshToken);

                return $refreshToken;
            } catch (UniqueTokenIdentifierConstraintViolationException $e) {
                if ($maxGenerationAttempts === 0) {
                    throw $e;
                }
            }
        }

        throw new Exception('Could not generate a unique refresh token identifier.');
    }

    public function createPassportTokenByUser(User $user, \App\Models\Client $client, $scopes = []): array
    {
        $idToken = new IdTokenResponse;

        $scopeEntities = [];
        //        $tokenCan = config('openid.passport.tokens_can');

        // TODO: 如果 scope 不在 tokensCan，则报错
        //        foreach ($scopes as $scope) {
        //            if (! in_array($scope, $tokenCan)) {
        //                abort(400, 'Scope not allowed');
        //            }
        //        }

        foreach ($scopes as $scope) {
            $scopeEntities[] = new Scope($scope);
        }

        $client = new Client($client->id,
            $client->name, $client->redirect, $client->secret != null, $client->provider);
        $accessToken = new AccessTokenResponse($user->id, $scopeEntities, $client);

        $now = new DateTimeImmutable;

        try {
            $accessToken->setIdentifier($this->generateUniqueIdentifier());
        } catch (OAuthServerException $e) {
            abort(500, 'Could not generate a unique access token identifier.');
        }
        $accessToken->setExpiryDateTime($now->add(Passport::tokensExpireIn()));

        $accessTokenRepository = new AccessTokenRepository(new TokenRepository, new Dispatcher);
        try {
            $accessTokenRepository->persistNewAccessToken($accessToken);
        } catch (UniqueTokenIdentifierConstraintViolationException $e) {
            abort(500, 'Could not generate a unique access token identifier.');
        }
        try {
            $refreshToken = $this->issueRefreshToken($accessToken);
        } catch (UniqueTokenIdentifierConstraintViolationException $e) {
            abort(500, 'Could not generate a unique refresh token identifier.');
        }

        $idTokenBuilder = $idToken->getExtraParams($accessToken);
        //        dd($idTokenBuilder);

        $r = [
            'access_token' => $accessToken->toString(),
            'refresh_token' => $refreshToken,
        ];

        $r['id_token'] = $idTokenBuilder;

        return $r;
    }

    protected function sendBearerTokenResponse($accessToken, $refreshToken): MessageInterface|ResponseInterface
    {
        $response = new BearerTokenResponse;
        $response->setAccessToken($accessToken);
        $response->setRefreshToken($refreshToken);

        $privateKey = new CryptKey('file://'.Passport::keyPath('oauth-private.key'));

        $response->setPrivateKey($privateKey);
        $response->setEncryptionKey(app('encrypter')->getKey());

        return $response->generateHttpResponse(new Response);
    }

    /**
     * @param  bool  $output  default = true
     */
    protected function getBearerTokenByUser(User $user, $clientId, bool $output = true): array|BearerTokenResponse
    {
        $passportToken = $this->createPassportTokenByUser($user, $clientId);
        $bearerToken = $this->sendBearerTokenResponse($passportToken['access_token'], $passportToken['refresh_token']);

        if (! $output) {
            $bearerToken = json_decode($bearerToken->getBody()->__toString(), true);
        }

        return $bearerToken;
    }
}
