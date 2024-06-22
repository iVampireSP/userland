<?php

declare(strict_types=1);

return [
    'passport' => [

        /**
         * Place your Passport and OpenID Connect scopes here.
         * To receive an `id_token, you should at least provide the openid scope.
         */
        'tokens_can' => [
            'openid' => '启用 OpenID 支持（将会获取 id_token）',
            'profile' => '获取基本信息',
            'email' => '获取电子邮件地址',
            'phone' => '获取手机号',
            'address' => '获取通信地址',
            'realname' => '获取用户的实名信息（包括姓名、身份证号）',
        ],
    ],

    /**
     * Place your custom claim sets here.
     */
    'custom_claim_sets' => [
        'login' => [
            'last-login',
        ],
        // 'company' => [
        //     'company_name',
        //     'company_address',
        //     'company_phone',
        //     'company_email',
        // ],
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
