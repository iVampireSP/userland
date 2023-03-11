<?php

return [
    'wecom' => [
        'robot_hook' => [
            'default' => env('WECOM_ROBOT_HOOK_DEFAULT', ''),
            'billing' => env('WECOM_ROBOT_HOOK_BILLING', ''),
            'cluster_ready' => env('WECOM_ROBOT_HOOK_CLUSTER_READY', ''),
        ],
    ],
    'supports' => [
        'pay' => [
            // 地址
            'url' => env('SUPPORT_PAY_URL', ''),
            // 商户 ID
            'mch_id' => env('SUPPORT_PAY_MCH_ID', ''),
            // 商户密钥
            'mch_key' => env('SUPPORT_PAY_MCH_KEY', ''),
        ],
        'real_name' => [
            'code' => env('SUPPORT_REAL_NAME_APP_CODE'),
            'min_age' => env('SUPPORT_REAL_NAME_MIN_AGE', 1),
            'max_age' => env('SUPPORT_REAL_NAME_MAX_AGE', 80),
            'price' => env('SUPPORT_REAL_NAME_PRICE', '0.01'),
        ],
    ],
];
