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
     * @var array
     */
    protected $policies = [
        SocialAccount::class => SocialAccountPolicy::class,
    ];

    public function bootingPackage()
    {
        $this->registerPolicies();
    }

    /**
     * Register the facade.
     */
    public function packageRegistered()
    {
        $this->app->bind('social-accounts', SocialAccounts::class);
    }

    /**
     * Register the application's policies.
     */
    public function registerPolicies()
    {
        foreach ($this->policies as $key => $value) {
            Gate::policy($key, $value);
        }
    }
}
