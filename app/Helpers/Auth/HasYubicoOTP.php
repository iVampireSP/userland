<?php

namespace App\Helpers\Auth;

use App\Models\YubicoOTP;
use Exception;

trait HasYubicoOTP
{
    /**
     * @throws Exception
     */
    public function addYubicoOTPDevice(string $device_id): YubicoOTP
    {
        // 检测是否被其他绑定
        if ($this->isRegisteredByModel($device_id)) {
            throw new Exception('该设备已被其他用户绑定');
        }

        $y = new YubicoOTP;

        $y->create([
            'device_id' => $device_id,
            'model_type' => get_class($this),
            'model_id' => $this->id,
        ]);

        return $y;
    }

    public function getYubicoOTPDevice(): array
    {
        return YubicoOTP::whereModelType(get_class($this))->whereModelId($this->id)
            ->get();
    }

    public function removeYubicoOTPDevice(string $device_id): bool
    {
        return YubicoOTP::whereModelType(get_class($this))->whereModelId($this->id)
            ->whereDeviceId($device_id)
            ->delete();
    }

    // remove all
    public function removeAllYubicoOTPDevice(): bool
    {
        return YubicoOTP::whereModelType(get_class($this))->whereModelId($this->id)
            ->delete();
    }

    public function hasYubicoOTPDevice(string $device_id): bool
    {
        return YubicoOTP::whereModelType(get_class($this))->whereModelId($this->id)
            ->whereDeviceId($device_id)
            ->exists();
    }

    public function hasYubicoOTP(): bool
    {
        return YubicoOTP::whereModelType(get_class($this))->whereModelId($this->id)
            ->exists();
    }

    // 是否被其他模型注册过
    public function isRegisteredByModel(string $device_id): bool
    {
        return YubicoOTP::whereModelType(get_class($this))->whereDeviceId($device_id)
            ->exists();
    }

    public function findByDeviceId(string $device_id): ?self
    {
        $y = YubicoOTP::whereModelType(get_class($this))->whereDeviceId($device_id)
            ->first();

        if (! $y) {
            return null;
        }

        return $this->where('id', $y->model_id)->first();
    }
}
