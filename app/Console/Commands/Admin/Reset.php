<?php

namespace App\Console\Commands\Admin;

use App\Contracts\YubicoOTP;
use App\Models\Admin;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class Reset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:reset';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '重置管理员的 Yubico OTP 设备';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // 获取管理员ID
        $id = $this->ask('请输入管理员 ID');

        // 获取管理员
        $admin = (new Admin)->find($id);

        // 验证管理员
        if (is_null($admin)) {
            $this->error('管理员不存在。');

            return CommandAlias::FAILURE;
        }

        // 新 OTP
        $otp = $this->ask('请输入新的 OTP');

        $yubico = app(YubicoOTP::class);

        $yubico->setOTP($otp);

        if (! $yubico->verify()) {
            $this->error('OTP 验证失败。');

            return CommandAlias::FAILURE;
        }

        $device_id = $yubico->getDeviceID();

        if ($admin->isRegisteredByModel($device_id)) {
            $this->error('该设备已经注册过管理员 '.$admin->findByDeviceID($device_id)->name.'。');

            return CommandAlias::FAILURE;
        }

        $admin->removeAllYubicoOTPDevice();

        try {
            $admin->addYubicoOTPDevice($device_id);
        } catch (\Exception $e) {
            $this->error('创建 Yubico OTP 设备失败。'.$e->getMessage());

            return CommandAlias::FAILURE;
        }

        // 输出信息
        $this->info('管理员 OTP 重置成功。');

        return CommandAlias::SUCCESS;
    }
}
