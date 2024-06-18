<?php

declare(strict_types=1);

return [
    'passport' => [

        /**
         * Place your Passport and OpenID Connect scopes here.
         * To receive an `id_token, you should at least provide the openid scope.
         */
        'tokens_can' => [
            'openid' => 'Enable OpenID Connect',
            'profile' => 'Information about your profile',
            'email' => 'Information about your email address',
            'phone' => 'Information about your phone numbers',
            'address' => 'Information about your address',
            // 'login' => 'See your login information',
            'realname' => '获取用户的实名信息（包括姓名、身份证号）',
            'user' => '获取用户的基本信息',
            'login' => '允许生成快速登录链接',
        ],
    ],

    /**
     * Place your custom claim sets here.
     */
    'custom_claim_sets' => [
        'login' => [
            'last-login',
        ],
        'company' => [
            'company_name',
            'company_address',
            'company_phone',
            'company_email',
        ],
    ],

    /**
     * You can override the repositories below.
     */
    'repositories' => [
        'identity' => \OpenIDConnect\Repositories\IdentityRepository::class,
        // 'scope' => \OpenIDConnect\Repositories\ScopeRepository::class,
        'scope' => \App\Support\ScopeRepository::class,

    ],

    /**
     * The signer to be used
     * Can be Ecdsa, Hmac or RSA
     */
    'signer' => \Lcobucci\JWT\Signer\Hmac\Sha256::class,
];
