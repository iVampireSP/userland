<?php

namespace App\Providers;

use App\Contracts\SMS;
use App\Models\Admin;
use App\Models\Client;
use App\Models\User;
use App\Observers\UserObserver;
use App\Support\RemovableRoutesMixin;
use App\Support\SMSSupport;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;
use Laravel\Pulse\Facades\Pulse;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(SMS::class, SMSSupport::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 如果在 dev
        if ($this->app->environment('local')) {
            $this->useStoragePassportKeys();
        }

        Route::mixin(new RemovableRoutesMixin());
        Route::removeGet('/oauth/authorize');

        Paginator::useBootstrapFive();

        Passport::useClientModel(Client::class);
        Passport::enableImplicitGrant();

        $this->registerObservers();
        $this->registerScopes();
        $this->setupPulse();
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
        Passport::tokensCan(config('openid.passport.tokens_can'));
        Passport::setDefaultScope([
            'profile',
        ]);
    }

    private function useStoragePassportKeys(): void
    {
        config(['passport.private_key' => file_get_contents(storage_path('oauth-private.key'))]);
        config(['passport.public_key' => file_get_contents(storage_path('oauth-public.key'))]);
    }

    private function setupPulse(): void
    {
        Pulse::user(fn (User $user) => [
            'name' => $user->name,
            'extra' => $user->email,
            'avatar' => $user->avatar(),
        ]);
        Gate::forUser(auth('admin')->user())->define('viewPulse', function (Admin $admin) {
            return true;
        });
    }
}
