<?php

namespace audunru\SocialAccounts\Tests;

use audunru\SocialAccounts\SocialAccountsServiceProvider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\SocialiteServiceProvider;
use Orchestra\Database\ConsoleServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function getPackageProviders($app)
    {
        return [SocialAccountsServiceProvider::class, ConsoleServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', str_random(32));
        $app->register(SocialiteServiceProvider::class);
    }

    protected function getPackageAliases($app)
    {
        return [
            'Socialite' => Socialite::class,
        ];
    }

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->withFactories(__DIR__.'/../tests/database/factories');
        $this->loadMigrationsFrom(__DIR__.'/../tests/database/migrations');
        $this->artisan('migrate');
    }

    public function enableUserCreation()
    {
        config(['social-accounts.automatically_create_users' => true]);
    }

    public function disableUserCreation()
    {
        config(['social-accounts.automatically_create_users' => false]);
    }

    public function enableSocialAccountCreation()
    {
        config(['social-accounts.users_can_add_social_accounts' => true]);
    }

    public function disableSocialAccountCreation()
    {
        config(['social-accounts.users_can_add_social_accounts' => false]);
    }
}
