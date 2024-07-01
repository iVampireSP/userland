<?php

namespace App\Support;

use OpenIDConnect\Interfaces\IdentityEntityInterface;
use OpenIDConnect\Interfaces\IdentityRepositoryInterface;

class IdentityRepositorySupport implements IdentityRepositoryInterface
{
    public function getByIdentifier(string $identifier): IdentityEntityInterface
    {
        $identityEntity = new IdentitySupport;
        $identityEntity->setIdentifier($identifier);

        return $identityEntity;
    }
}
