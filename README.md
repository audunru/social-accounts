# Social Accounts for Laravel

[![Build Status](https://travis-ci.org/audunru/social-accounts.svg?branch=master)](https://travis-ci.org/audunru/social-accounts)
[![Coverage Status](https://coveralls.io/repos/github/audunru/social-accounts/badge.svg?branch=master)](https://coveralls.io/github/audunru/social-accounts?branch=master)
[![StyleCI](https://github.styleci.io/repos/200706096/shield?branch=master)](https://github.styleci.io/repos/200706096)

Add social login (Google, Facebook, and others) to your Laravel app.

This package uses [Laravel Socialite](https://github.com/laravel/socialite) to authenticate users, and takes care of storing the provider (eg. Google) and provider user ID (eg. 123456789) as a SocialAccount (a related model of the User model).

Your users can add one or more social logins to their account. It's up to you if you want them to sign up with a normal username and password first, or if they can sign up just by signing in with a provider.

The package also has a JSON API so you can display which social accounts a user has logged in with, and allow them to remove them.

# Installation

## Step 1: Install with Composer

```bash
composer require audunru/social-accounts
```

## Step 2: Make changes to your code

First, you must add the `HasSocialAccounts` trait to your `User` model:

```php
use audunru\SocialAccounts\Traits\HasSocialAccounts;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasSocialAccounts;
    /**
     * Get user who has logged in with Google account ID 123456789
     * $user = User::findBySocialAccount('google', '123456789')
     *
     * Retrieve all social accounts belonging to $user
     * $user->socialAccounts
     */
}
```

Second, you need to specify which providers you are going to support. Publish the configuration, and open up `config/social-accounts.php` and add them to the array.

```bash
php artisan vendor:publish --provider="audunru\SocialAccounts\SocialAccountsServiceProvider" --tag=config
```

There is an array called "providers" where you can specify the ones you want:

```php
'providers' => [
    // 'bitbucket',
    // 'facebook',
    // 'github',
    // 'gitlab',
    'google',
    // 'linkedin',
    // 'twitter',
],
```

Third, you need to add credentials for your supported social login providers to `config/services.php`. To login with Google, you would add the following to `config/services.php`:

```php
'google' => [
    'client_id' => env('GOOGLE_CLIENT_ID'), // Get your client ID and secret from https://console.developers.google.com
    'client_secret' => env('GOOGLE_CLIENT_SECRET'),
    // Note: The "redirect" setting will be configured automatically. You are not required to add it to services.php yourself. If you don't want to use this package's default routes, you will need to configure it. If so, the package will use the value from services.php.
    // 'redirect' => '/social-accounts/login/google/callback',
],
```

Fourth, you should call the `SocialAccounts::routes` method within the boot method of your AuthServiceProvider. This method will register the routes necessary to login with your configured providers. It will also register the API routes necessary for a user to retrieve their social accounts and remove them.

```php
<?php

namespace App\Providers;

use audunru\SocialAccounts\SocialAccounts;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     */
    public function boot()
    {
        $this->registerPolicies();

        SocialAccounts::routes();
    }
}
```

Optional: You can add web and API routes in separate steps.

```php
SocialAccounts::routes(
    function ($router) {
        $router->forWeb();
    }
);
SocialAccounts::routes(
    function ($router) {
        $router->forApi();
    }
);
```

## Step 3: Configuration and customization

You can find the configuration and documentation of all options in [config/social-accounts.php](config/social-accounts.php).

## Step 4: Run migrations

Optional: Before running the migrations, you can publish them with this command:

```bash
php artisan vendor:publish --provider="audunru\SocialAccounts\SocialAccountsServiceProvider" --tag=migrations
```

Run migrations with this command:

```bash
php artisan migrate
```

The migrations will create a `social_accounts` table, which will hold all added social accounts.

If you set the "automatically_create_users" option in `config/social-accounts.php` to `true`, the `email` and `password` columns in your `users` table will be made nullable. Not all providers require users to have an email address, and the `password` column must be nullable because users who sign up this way won't have password.

# Usage

## Adding social login to existing users

If you want to allow your existing users to log in with Google, add a link to `/social-accounts/login/google` somewhere in your application:

```html
@auth
<a href="/social-accounts/login/google">Add Google login to my account</a>
@endauth
```

After clicking on this link, the user will be redirected to Google, where they must authorize the request. Afterwards they will be returned to your app. Then, a new `SocialAccount` will be added as related model of the `User`.

## Signing up users

If you want to allow users to sign up with this package, you must first publish the configuration file and then set `automatically_create_users` to `true`.

```php
'automatically_create_users' => true,
```

Then, run the migrations so that the email and password columns are made nullable.

```bash
php artisan migrate
```

Then add a link to `/social-accounts/login/google`:

```html
<a href="/social-accounts/login/google">Sign up with Google</a>
```

## Logging in

For users who are not signed in, simply add a link to `/social-accounts/login/google`:

```html
<a href="/social-accounts/login/google">Sign in with Google</a>
```

## API

The JSON API, which by default is accessible at `/social-accounts`, allows authenticated users to retrieve their social accounts and remove them.

To retrieve an array of social accounts, make a GET request to `/social-accounts`.

To retrieve a single social account, make a GET request to `/social-accounts/123`, where 123 is the ID of the account.

To delete a social account, make a DELETE request to `/social-accounts/123`.

Users can't update social accounts through the API, they will have to delete them first and then authorize again.

## Optional Parameters, Access Scopes and Stateless Authentication

To include optional parameters in the request, call the `registerProviderSettings` method on the facade. The method takes three parameters, the provider name, the name of the method to call on the provider object, and an array of parameters.

For example, you can use this to limit the domains a Google user can choose to sign in with to just one:

```php
SocialAccounts::registerProviderSettings('google', 'with', ['hd' => 'seinfeld.com']);
```

[See Socialite's documentation for more info on Optional Parameters, Access Scopes and Stateless Authentication](https://laravel.com/docs/5.8/socialite#optional-parameters)

## Gates

You can use [gates](https://laravel.com/docs/5.8/authorization#gates) to allow or deny certain actions. Gates should be defined within the boot method of your AuthServiceProvider.

```php
<?php

namespace App\Providers;

use App\User;
use audunru\SocialAccounts\SocialAccounts;
use Laravel\Socialite\Contracts\User as ProviderUser;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register any authentication / authorization services.
     */
    public function boot()
    {
        $this->registerPolicies();
        /*
         * If your company uses G Suite and you want to ensure that only employees can log in, you can define a "login-with-provider" gate.
         *
         * If you don't define this gate, any ProviderUser is allowed to pass through.
         */
        Gate::define('login-with-provider', function (?User $user, ProviderUser $providerUser) {
            /*
             * $providerUser->user['hd'] contains the domain name the Google account belongs to.
             *
             * It's good practice to verify that the account does in fact belong to your company after the user has authorized with Google and returned to your application.
             */
            return 'company.com' === $providerUser->user['hd'];
        });
        /*
         * If you want to restrict who can add social accounts, you can define a "add-social-account" gate.
         *
         * If you don't define this gate, any authenticated user can add a social account.
         */
        Gate::define('add-social-account', function (User $user, ProviderUser $providerUser) {
            /*
             * isAdmin() is a hypothetical method you could define on your User model.
             *
             * In this case, only administrators would be allowed to add social accounts.
             */
            return $user->isAdmin();
        });
        SocialAccounts::routes();
    }
}

```

## Events

When a user is created after authenticating with a provider, a `SocialUserCreated` event is dispatched. The event receives the User, SocialAccount and ProviderUser model.

When a social account is added to an existing user, a `SocialAccountAdded` event is dispatched. The event receives the User, SocialAccount and ProviderUser model.

For instance, you could listen for an event and grab more [details about the user](https://laravel.com/docs/5.8/socialite#retrieving-user-details).

```php
<?php

namespace App\Listeners;

use audunru\SocialAccounts\Events\SocialUserCreated;

class AddUserAvatar
{
    /**
     * Handle the event.
     *
     * @param  SocialUserCreated  $event
     * @return void
     */
    public function handle(SocialUserCreated $event)
    {
        /*
         * The package only saves the user's name and email when creating a new user.
         *
         * By listening for the event, we can grab more details about the user.
         */
        $event->user->update([
            'avatar' => $event->providerUser->getAvatar();
        ]);
    }
}
```

# Alternatives

[Easy Socialite](https://github.com/MiloudiMohamed/easy-socialite)

# Development

## Testing

Run tests:

```bash
composer test
```
