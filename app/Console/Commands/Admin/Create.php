<?php

namespace App\Console\Commands\Admin;

use App\Contracts\YubicoOTP;
use App\Models\Admin;
use Illuminate\Console\Command;
use Symfony\Component\Console\Command\Command as CommandAlias;

class Create extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '创建一个管理员账号。';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // 按一下 Yubico 来输入 OTP
        $otp = $this->ask('请输入 Yubico OTP');
        if (! $otp) {
            $this->error('OTP 不能为空。');

            return CommandAlias::FAILURE;
        }

        $yubico = app(YubicoOTP::class);

        $yubico->setOTP($otp);

        if (! $yubico->verify()) {
            $this->error('OTP 验证失败。');

            return CommandAlias::FAILURE;
        }

        $device_id = $yubico->getDeviceID();

        $admin = new Admin;

        if ($admin->isRegisteredByModel($device_id)) {
            $this->error('该设备已经注册过管理员 '.$admin->findByDeviceID($device_id)->name.'。');

            return CommandAlias::FAILURE;
        }

        // 名称
        $name = $this->ask('请输入名称');
        $email = $this->ask('请输入邮箱');

        // 创建管理员
        $admin = (new Admin)->create([
            'name' => $name,
            'email' => $email,
        ]);

        try {
            $admin->addYubicoOTPDevice($device_id);
        } catch (\Exception $e) {
            $this->error('创建 Yubico OTP 设备失败。'.$e->getMessage());

            return CommandAlias::FAILURE;
        }

        // 输出信息
        $this->info('管理员创建成功，ID 为: '.$admin->id.'。');

        return CommandAlias::SUCCESS;
    }
}
