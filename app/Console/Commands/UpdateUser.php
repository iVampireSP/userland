<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Str;

class UpdateUser extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update-user';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '更新用户的基本信息, 比如邮箱 md5 等.';

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        // 寻找 email_md5 为空的用户

        $this->info('开始更新用户的基本信息...');

        $this->warn('更新用户的邮箱 md5 值...');
        User::whereNull('email_md5')->chunk(100, function ($users) {
            /* @var User $user */
            foreach ($users as $user) {
                $user->email_md5 = md5($user->email);
                $user->save();
            }
        });
        $this->info('更新用户的邮箱 md5 值完成!');

        // 寻找 uuid 为空的用户
        $this->warn('更新用户的 uuid 值...');
        User::whereNull('uuid')->chunk(100, function ($users) {
            /* @var User $user */
            foreach ($users as $user) {
                $user->uuid = Str::uuid();
                $user->save();
            }
        });
        $this->info('更新用户的 uuid 值完成!');

        $this->info('更新用户的基本信息完成!');
    }
}
