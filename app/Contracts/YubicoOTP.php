<?php

namespace App\Contracts;

interface YubicoOTP
{
    public function setOTP(string $otp): self;

    public function getDeviceID(): string;

    public function verify(): bool;
}
