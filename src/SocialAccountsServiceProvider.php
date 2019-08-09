<?php

namespace audunru\SocialAccounts;

use audunru\SocialAccounts\Models\SocialAccount;
use audunru\SocialAccounts\Policies\SocialAccountPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class SocialAccountsServiceProvider extends ServiceProvider
{
    protected $policies = [
        SocialAccount::class => SocialAccountPolicy::class,
    ];

    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        $this->registerPolicies();

        $this->mergeConfigFrom(
            __DIR__.'/../config/social-accounts.php', 'social-accounts'
        );

        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__.'/../database/migrations');

            $this->publishes([
                __DIR__.'/../config/social-accounts.php' => config_path('social-accounts.php'),
            ], 'config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'migrations');
        }
    }

    /**
     * Register the facade.
     */
    public function register()
    {
        $this->app->bind('social-accounts', SocialAccounts::class);
    }
}
