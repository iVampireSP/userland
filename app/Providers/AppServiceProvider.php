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
        Passport::tokensExpireIn(now()->addMinutes(config('passport.token_lifetime.token')));
        Passport::refreshTokensExpireIn(now()->addMinutes(config('passport.token_lifetime.refresh_token')));
        Passport::personalAccessTokensExpireIn(now()->addMinutes(config('passport.token_lifetime.personal_access_token')));
        Passport::setDefaultScope('openid');
    }
}
