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
use Monolog\Formatter\ElasticsearchFormatter;
use Monolog\Handler\ElasticsearchHandler;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // $index = config("logging.channels.elastic.index");
        // $type = "_doc";

        // $this->app->bind(Client::class, function ($app) {

        //     return ClientBuilder::create()->setHosts(config("logging.channels.elastic.hosts"))->build();
        // });
        // $this->app->bind(ElasticsearchFormatter::class, function ($app) use ($index, $type) {
        //     return new ElasticsearchFormatter($index, $type);
        // });

        // $this->app->bind(ElasticsearchHandler::class, function ($app) use ($index, $type) {
        //     return new ElasticsearchHandler($app->make(Client::class), [
        //         'index'        => $index,
        //         'type'         => $type,
        //         'ignore_error' => false,
        //     ]);
        // });

        
        $this->app->singleton("elasticsearch-log-handler", function (Application $app, array $config = []) {
            unset($app);
            $builder = ClientBuilder::create()->setHosts($config['hosts'])
                ->setSSLVerification($config['verify_ssl']);


            if ($config['pass']) {
                $builder->setBasicAuthentication($config['user'], $config['pass']);
            }

            $builder = $builder->build();


            if (!$builder->indices()->exists(['index' => $config['index']])) {
                $builder->indices()->create([
                    'index' => $config['index'],
                ]);
            }

            return $builder;

            // return;

            // $builder = ClientBuilder::create()->setHosts($params['hosts'])
            //     ->setSSLVerification(config($params['verify_ssl']));

            // if ($params['pass']) {
            //     $builder->setBasicAuthentication($params['user'], $params['pass']);
            // }

            // return $builder->build();
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
