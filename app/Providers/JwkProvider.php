<?php

namespace App\Providers;

use App\Support\IdTokenResponse;
use Illuminate\Encryption\Encrypter;
use Laravel\Passport\Bridge\AccessTokenRepository;
use Laravel\Passport\Bridge\ClientRepository;
use Laravel\Passport\Bridge\ScopeRepository;
use Laravel\Passport\PassportServiceProvider;
use League\OAuth2\Server\AuthorizationServer;

class JwkProvider extends PassportServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        parent::register();
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        parent::boot();
    }

    public function makeAuthorizationServer(): AuthorizationServer
    {
        $cryptKey = $this->makeCryptKey('private');
        $encryptionKey = app(Encrypter::class)->getKey();

        return new AuthorizationServer(
            clientRepository: app(ClientRepository::class),
            accessTokenRepository: app(AccessTokenRepository::class),
            scopeRepository: app(ScopeRepository::class),
            privateKey: $cryptKey,
            encryptionKey: $encryptionKey,
            responseType: new IdTokenResponse()
        );
    }
}
