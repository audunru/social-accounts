# v10 to v11

## No login events when a social account is added

In earlier versions, when a logged-in user added a new social account, the callback called `Auth::login()` again and dispatched `SocialUserLoggedIn`. Thus Laravel's `Login` event was also dispatched, and with `?remember` a remember cookie was set.

Now the callback does not call `Auth::login()` and does not dispatch `SocialUserLoggedIn` when a social account is added. Only `SocialAccountAdded` is dispatched. The user stays logged in.

If a listener for `SocialUserLoggedIn` or `Login` must also run when a social account is added, listen for `SocialAccountAdded` too.

# v9 to v10

## Logged-in users can no longer log in as another user

In earlier versions, when a logged-in user authenticated with a provider account that belonged to another user, the callback logged in the other user. Now the callback throws `audunru\SocialAccounts\Exceptions\SocialAccountBelongsToAnotherUser` (HTTP 409), and the logged-in user does not change.

Version 9.2.1 also has this change. It was released as a patch by mistake.

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
