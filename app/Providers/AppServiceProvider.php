<?php

namespace App\Providers;

use App\Models\Client;
use App\Models\User;
use App\Observers\UserObserver;
use Elastic\Elasticsearch\ClientBuilder;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Illuminate\Contracts\Foundation\Application;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton("elasticsearch-log-handler", function () {
            $builder = ClientBuilder::create()->setHosts(config("logging.channels.elastic.hosts"))
                ->setSSLVerification(config('logging.channels.elastic.verify_ssl'));

            if (config("logging.channels.elastic.pass")) {
                $builder->setBasicAuthentication(config("logging.channels.elastic.user"), config("logging.channels.elastic.pass"));
            }

            return $builder->build();
        });


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
