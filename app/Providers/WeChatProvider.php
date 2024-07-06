<?php

namespace App\Providers;

use EasyWeChat\OfficialAccount\Application as OfficialAccount;
use Illuminate\Support\ServiceProvider;

class WeChatProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->app->singleton(OfficialAccount::class, function () {
            return new OfficialAccount(config('wechat'));
        });
        $this->app->alias(OfficialAccount::class, 'easywechat.official_account');
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
