<?php

namespace audunru\SocialAccounts;

use audunru\SocialAccounts\Models\SocialAccount;
use audunru\SocialAccounts\Policies\SocialAccountPolicy;
use Illuminate\Support\Facades\Gate;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

class SocialAccountsServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('social-accounts')
            ->hasConfigFile()
            ->hasMigrations('create_social_accounts_table', 'make_email_and_password_nullable');
    }

    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        SocialAccount::class => SocialAccountPolicy::class,
    ];

    public function bootingPackage(): void
    {
        $this->registerPolicies();
    }

    /**
     * Register the facade.
     */
    public function packageRegistered(): void
    {
        $this->app->bind('social-accounts', SocialAccounts::class);
    }

    /**
     * Register the application's policies.
     */
    public function registerPolicies(): void
    {
        foreach ($this->policies as $key => $value) {
            Gate::policy($key, $value);
        }
    }
}
