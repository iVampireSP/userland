<?php

declare(strict_types=1);

namespace App\Support\OAuth;

use Nyholm\Psr7\Factory\Psr17Factory;
use Psr\Http\Message\ServerRequestInterface;
use Symfony\Bridge\PsrHttpMessage\Factory\PsrHttpFactory;

class LaravelCurrentRequestService
{
    public function getRequest(): ServerRequestInterface
    {
        return (new PsrHttpFactory(
            new Psr17Factory,
            new Psr17Factory,
            new Psr17Factory,
            new Psr17Factory
        ))->createRequest(request());
    }
}
