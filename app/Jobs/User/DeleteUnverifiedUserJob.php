<?php

namespace App\Jobs\User;

use App\Jobs\UserDeleteJob;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteUnverifiedUserJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        // 删除注册时间超过 3 天的未验证邮箱或手机号的用户。
        (new User)->whereNull('email_verified_at')->whereNull('phone_verified_at')->where('created_at', '<', now()->subDays(3))->chunk(100, function ($users) {
            $users->each(function ($user) {
                self::dispatch(new UserDeleteJob($user));
            });
        });
    }
}
