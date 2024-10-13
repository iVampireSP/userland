<?php

namespace App\Console\Commands;

use App\Models\EmailBlacklist;
use Illuminate\Console\Command;

class SeedEmailBlacklist extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'email:blacklist-seed';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '创建或更新邮箱域名黑名单列表';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        // Check if the email_blacklists table has any data
        if (EmailBlacklist::exists()) {
            // If table is not empty, truncate it (clear all data)
            $this->info('邮箱域名黑名单表不为空。正在清空并重新获取...');

            EmailBlacklist::truncate(); // Clear the table

            $this->info('表清空成功，正在插入数据...');
        } else {
            $this->info('邮箱域名黑名单表为空。正在插入数据...');
        }

        // Run the seeder to fill the email_blacklist table
        $this->call('db:seed', ['--class' => 'EmailBlacklistSeeder', '--force' => true]);

        $this->info('邮箱域名黑名单表插入数据成功');

        return 0;
    }
}
