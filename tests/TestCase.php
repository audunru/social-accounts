<?php

namespace audunru\SocialAccounts\Tests;

use audunru\SocialAccounts\Facades\SocialAccounts;
use audunru\SocialAccounts\SocialAccountsServiceProvider;
use audunru\SocialAccounts\Tests\Models\User;
use CreateSocialAccountsTable;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Gate;
use Laravel\Socialite\Contracts\User as ProviderUser;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\SocialiteServiceProvider;
use MakeEmailAndPasswordNullable;
use Mockery;
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

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->loadMigrationsFrom(__DIR__.'/../tests/database/migrations');
        $this->artisan('migrate');

        include_once __DIR__.'/../database/migrations/create_social_accounts_table.php.stub';
        include_once __DIR__.'/../database/migrations/make_email_and_password_nullable.php.stub';
        (new CreateSocialAccountsTable())->up();
        (new MakeEmailAndPasswordNullable())->up();

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

    public function requireLaravelVersion(string $version)
    {
        $laravelVersion = App::version();
        if (! version_compare($laravelVersion, $version, '>=')) {
            $this->markTestSkipped("Test requires at least Laravel {$version}, but current version is {$laravelVersion}");
        }
    }

    public function mockSocialite(string $email = 'art@vandelayindustries.com', string $name = 'Art Vandelay', string $provider_user_id = 'amazing-id')
    {
        // Mock a user which the provider will return
        $providerUser = Mockery::mock('Laravel\Socialite\Contracts\User');

        $providerUser
            ->shouldReceive('getEmail')
            ->andReturn($email)
            ->shouldReceive('getName')
            ->andReturn($name)
            ->shouldReceive('getId')
            ->andReturn($provider_user_id);

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
