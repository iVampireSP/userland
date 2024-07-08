<?php

$otp_servers = [];

if (env('YUBIKEY_OTP_SERVERS')) {
    $otp_servers = explode(',', env('YUBIKEY_OTP_SERVERS'));
} else {
    $otp_servers = [
        'https://api.yubico.com/wsapi/2.0/verify',
        'https://api2.yubico.com/wsapi/2.0/verify',
        'https://api3.yubico.com/wsapi/2.0/verify',
        'https://api4.yubico.com/wsapi/2.0/verify',
        'https://api5.yubico.com/wsapi/2.0/verify',
    ];
}

return [
    'otp_servers' => $otp_servers,
    'client_id' => env('YUBIKEY_CLIENT_ID', ''),
    'client_secret' => env('YUBIKEY_CLIENT_SECRET', ''),
];
