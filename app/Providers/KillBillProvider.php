<?php

namespace App\Providers;

use EasyWeChat\OfficialAccount\Application as OfficialAccount;
use Illuminate\Support\ServiceProvider;
use Killbill\Client\KillbillClient;

class KillBillProvider extends ServiceProvider
{
    public function register(): void
    {
        parent::register();
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
        $this->app->singleton(KillbillClient::class, function () {
            return new KillbillClient(
                logger: null,
                host: config('killbill.url'),
                username: config('killbill.username'),
                password: config('killbill.password')
            );
        });
    }
}
