<?php

declare(strict_types=1);

return [
    'kid' => 1,

    'passport' => [
        'tokens_can' => [
            //            'openid' => '启用 OpenID 支持（将会获取 id_token）',
            'profile' => '获取基本信息',
            'email' => '获取电子邮件地址',
            'phone' => '获取手机号',
            'address' => '获取通信地址（未实现）',
            'realname' => '获取用户的实名信息（包括姓名、身份证号）',
        ],
    ],

    //    /**
    //     * The signer to be used
    //     * Can be Ecdsa, Hmac or RSA
    //     */
    'signer' => \Lcobucci\JWT\Signer\Rsa\Sha256::class,

];
