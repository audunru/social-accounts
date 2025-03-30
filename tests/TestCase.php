<?php

namespace audunru\SocialAccounts\Tests;

use audunru\SocialAccounts\Facades\SocialAccounts;
use audunru\SocialAccounts\SocialAccountsServiceProvider;
use audunru\SocialAccounts\Tests\Models\User;
use CreateSocialAccountsTable;
use Illuminate\Config\Repository;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Laravel\Socialite\Contracts\User as ProviderUser;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\SocialiteServiceProvider;
use MakeEmailAndPasswordNullable;
use Mockery;
use Orchestra\Testbench\Attributes\WithMigration;
use Orchestra\Testbench\TestCase as BaseTestCase;

/**
 * @SuppressWarnings("unused")
 */
#[WithMigration]
abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [
            SocialAccountsServiceProvider::class,
            SocialiteServiceProvider::class,
        ];
    }

    protected function defineEnvironment($app)
    {
        tap($app['config'], function (Repository $config) {
            $config->set('auth.guards.api', [
                'driver'   => 'token',
                'provider' => 'users',
                'hash'     => false,
            ]);
            $config->set('social-accounts.api_middleware', ['api', 'auth:api']);
            $config->set('social-accounts.automatically_create_users', true);
            $config->set('social-accounts.models.user', User::class);
        });
    }

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

        include_once __DIR__.'/../database/migrations/create_social_accounts_table.php.stub';
        include_once __DIR__.'/../database/migrations/make_email_and_password_nullable.php.stub';
        (new CreateSocialAccountsTable())->up();
        (new MakeEmailAndPasswordNullable())->up();

        SocialAccounts::emptyProviderSettings();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        SocialAccounts::emptyProviderSettings();
    }

    protected function enableUserCreation()
    {
        config(['social-accounts.automatically_create_users' => true]);
    }

    protected function disableUserCreation()
    {
        config(['social-accounts.automatically_create_users' => false]);
    }

    protected function enableSocialAccountCreation()
    {
        Gate::define('add-social-account', function (User $user, ProviderUser $providerUser) {
            return true;
        });
    }

    protected function disableSocialAccountCreation()
    {
        Gate::define('add-social-account', function (User $user, ProviderUser $providerUser) {
            return false;
        });
    }

    protected function mockSocialite(string $email = 'art@vandelayindustries.com', string $name = 'Art Vandelay', string $providerUserId = 'amazing-id')
    {
        // Mock a user which the provider will return
        $providerUser = Mockery::mock('Laravel\Socialite\Contracts\User');

        $providerUser
            ->shouldReceive('getEmail')
            ->andReturn($email)
            ->shouldReceive('getName')
            ->andReturn($name)
            ->shouldReceive('getId')
            ->andReturn($providerUserId);

        // Mock a provider which Socialite will return
        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider
            ->shouldReceive('user')
            ->andReturn($providerUser)
            ->shouldReceive('redirect')
            ->andReturn('Redirecting...');

        // Mock Socialite
        Socialite::shouldReceive('driver')
            ->andReturn($provider);
    }
}
