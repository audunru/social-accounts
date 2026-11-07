# v9 to v10

## Logged-in users can no longer log in as another user

In earlier versions, when a logged-in user authenticated with a provider account that belonged to another user, the callback logged in the other user. Now the callback throws `audunru\SocialAccounts\Exceptions\SocialAccountBelongsToAnotherUser` (HTTP 409), and the logged-in user does not change.

If your app used this to switch accounts, log the user out before they log in with the provider. To show a message to the user, render the exception. See [Adding social login to existing users](README.md#adding-social-login-to-existing-users) for an example.

# v7 to v8

## `SocialAccounts::routes()` has been removed

You should remove any call to the `SocialAccounts::routes()` method. It has been removed in favour of adding routes automatically.

If you want to disable automatic registration of web and/or API routes, you can do so in the [config file](config/social-accounts.php).

## New configuration options

The [config file](config/social-accounts.php) contains new options which you should add to your existing config:

- api_path
- web_routes_enabled
- web_middleware
- api_routes_enabled
- api_middleware
