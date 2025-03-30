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
