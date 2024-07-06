<?php
return [
    'app_id'       => env('WECHAT_APPID', 'YourAppId'), // 必填
    'secret'       => env('WECHAT_SECRET', 'YourSecret'), // 必填
    'token'        => env('WECHAT_TOKEN', 'YourToken'),  // 必填
    'aes_key'      => env('WECHAT_AES_KEY', 'YourEncodingAESKey') // 加密模式需要，其它模式不需要
];