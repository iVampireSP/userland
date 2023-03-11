<?php

namespace App\Console;

use App\Jobs\User\DeleteUnverifiedUserJob;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // 删除注册超过 3 天未验证邮箱的用户
        $schedule->job(new DeleteUnverifiedUserJob())->daily()->onOneServer()->name('删除注册超过 3 天未验证邮箱的用户');
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
