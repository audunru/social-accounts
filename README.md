[![Build Status](https://travis-ci.org/audunru/social-accounts.svg?branch=master)](https://travis-ci.org/audunru/social-accounts)

# Social Accounts for Laravel

This package adds social login (Google, Facebook, and others) to your Laravel app.

It uses [Laravel Socialite](https://github.com/laravel/socialite) to authenticate users, and takes care of storing the provider (eg. Google) and provider user ID (eg. 123456789) as a related model of the User model.

This means that your users can add one or more social login to their account. It's up to you if you want them to sign up with a normal username and password first, or if they can create an account using only something like Google.

The package also provides a JSON API so you can display which social accounts a user has authenticated with, and allow them to remove them.

# Installation

## Step 1: Install with Composer

The package is not published on packagist.org yet, so for now you need to add the following to your `composer.json` before you can install it:

```json
"repositories": [
    {
        "url": "https://github.com/audunru/social-accounts.git",
        "type": "git"
    }
]
```

Afterwards, run this command:

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
     * With this trait, you can do things like:
     *
     * User::findBySocialAccount('google', '123456789')
     * (returns the user with that Google login)
     *
     * User::find(1)->socialAccounts
     * (returns all social accounts belonging to $user)
     */
}
```

Second, you need to specify which social login providers you are going to support. Publish the configuration, and open up `config/social-accounts.php` and add them to the array.

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
    'redirect' => '/social/login/google/callback',  // This route is registered by the package and should not be changed
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

Optionally, you may add just the web routes with:

```php
SocialAccounts::routes(
    function ($router) {
        $router->forWeb();
    }
);
```

API routes can be added with:

```php
SocialAccounts::routes(
    function ($router) {
        $router->forApi();
    }
);
```

## Step 3: Configuration and customization

You can change the configuration in `config/social-accounts.php`. Socialite´s configuration is in `config/services.php`.

Before running the migrations, you can publish them with this command (optional):

```bash
php artisan vendor:publish --provider="audunru\SocialAccounts\SocialAccountsServiceProvider" --tag=migrations
```

## Step 4: Run migrations

Run migrations with this command:

```bash
php artisan migrate
```

The migrations will create a `social_accounts` table, which will hold all added social accounts. They will also make the `email` and `password` columns in your `users` table nullable, because not all providers require users to have an email address. The `password` column must be nullable in case someone signs up for your app with this package, in which case they won't have a password.

# Usage

## Adding social login to existing users

If you want to allow your existing users to log in with Google, provide them with a link to `/social/login/google` somewhere in your application:

```html
@auth
<a href="/social/login/google">Add Google login to my account</a>
@endauth
```

After clicking on this link, the user will be redirected to Google, where they must authorize the request. Afterwards they will be returned to your app. Then, a new `SocialAccount` will be added as related model of the `User`.

## Signing up users

If you want to allow users to sign up with this package, you must first publish the configuration file and then set `automatically_create_users` to `true`:

```php
'automatically_create_users' => true,
```

Then provide them with a link to `/social/login/google`:

```html
<a href="/social/login/google">Sign up with Google</a>
```

## Logging in

For users who are not signed in, simply add a link to `/social/login/google`:

```html
<a href="/social/login/google">Sign in with Google</a>
```

## API

The JSON API, which by default is accessible at `/social/social-accounts`, allows authenticated users to retrieve their social accounts and remove them.

To retrieve an array of social accounts, make a GET request to `/social/social-accounts`.

To retrieve a single social account, make a GET request to `/social/social-accounts/123`, where 123 is the ID of the account.

To delete a social account, make a DELETE request to `/social/social-accounts/123`.

Users can't update social accounts through the API, they will have to delete them first and then authorize again.

# Alternatives

[Easy Socialite](https://github.com/MiloudiMohamed/easy-socialite)

# Development

## Testing

Run tests:

```bash
composer test
```
