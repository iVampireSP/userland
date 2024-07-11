<?php

namespace App\Support;

use App\Helpers\CustomClaimsAccessTokenTrait;
use Laravel\Passport\Bridge\AccessToken;

class AccessTokenResponse extends AccessToken
{
    use CustomClaimsAccessTokenTrait;
}
