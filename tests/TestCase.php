<?php

namespace audunru\SocialAccounts\Tests;

use Illuminate\Support\Facades\Gate;
use Laravel\Socialite\Facades\Socialite;
use audunru\SocialAccounts\Tests\Models\User;
use Orchestra\Database\ConsoleServiceProvider;
use Laravel\Socialite\SocialiteServiceProvider;
use Orchestra\Testbench\TestCase as BaseTestCase;
use audunru\SocialAccounts\Facades\SocialAccounts;
use Laravel\Socialite\Contracts\User as ProviderUser;
use audunru\SocialAccounts\SocialAccountsServiceProvider;

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
        // TODO: What here needs to run every test, and what should only run once?
        $this->withFactories(__DIR__.'/../tests/database/factories');
        $this->loadMigrationsFrom(__DIR__.'/../tests/database/migrations');
        $this->artisan('migrate');
        config(['social-accounts.models.user' => User::class]);
        SocialAccounts::routes();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        SocialAccounts::emptyProviderSettings();
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
        Gate::define('add-social-account', function (User $user, ProviderUser $providerUser) {
            return true;
        });
    }

    public function disableSocialAccountCreation()
    {
        Gate::define('add-social-account', function (User $user, ProviderUser $providerUser) {
            return false;
        });
    }
}
