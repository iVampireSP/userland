<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\User;
use App\Observers\UserObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Paginator::useBootstrapFive();

        Passport::useClientModel(Client::class);
        Passport::enableImplicitGrant();

        $this->registerObservers();
        $this->registerScopes();
    }

    private function registerObservers(): void
    {
        User::observe(UserObserver::class);
        // Subscription::observe(SubscriptionObserve::class);
    }

    private function registerScopes(): void
    {
        Passport::tokensCan([
            'realname' => '获取用户的实名信息（包括姓名、身份证号）',
            'user' => '获取用户的基本信息',
            'login' => '允许生成快速登录链接',
        ]);

        Passport::setDefaultScope([
            'user',
        ]);
    }
}
