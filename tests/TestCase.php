<?php

namespace audunru\SocialAccounts\Tests;

use audunru\SocialAccounts\Facades\SocialAccounts;
use audunru\SocialAccounts\SocialAccountsServiceProvider;
use audunru\SocialAccounts\Tests\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Laravel\Socialite\Contracts\User as ProviderUser;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\SocialiteServiceProvider;
use Mockery;

use function Orchestra\Testbench\package_path;

use Orchestra\Testbench\TestCase as BaseTestCase;

/**
 * @SuppressWarnings("unused")
 */
abstract class TestCase extends BaseTestCase
{
    use RefreshDatabase;

    protected function getPackageProviders($app)
    {
        return [SocialAccountsServiceProvider::class];
    }

    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.debug', 'true' === env('APP_DEBUG'));
        $app['config']->set('app.key', substr(str_shuffle(str_repeat('0123456789abcdefghijklmnopqrstuvwxyz', 5)), 0, 32));
        $app['config']->set('auth.guards.api', [
            'driver'   => 'token',
            'provider' => 'users',
            'hash'     => false,
        ]);
        $app->register(SocialiteServiceProvider::class);
    }

    protected function getPackageAliases($app)
    {
        return [
            'Socialite'      => Socialite::class,
            'SocialAccounts' => SocialAccounts::class,
        ];
    }

    protected function defineDatabaseMigrations()
    {
        $this->loadMigrationsFrom(
            package_path('database/migrations')
        );
    }

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();

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

        $this->artisan('migrate:refresh');
    }

    public function disableUserCreation()
    {
        config(['social-accounts.automatically_create_users' => false]);

        $this->artisan('migrate:refresh');
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

    public function mockSocialite(string $email = 'art@vandelayindustries.com', string $name = 'Art Vandelay', string $providerUserId = 'amazing-id')
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
