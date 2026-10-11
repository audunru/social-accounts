# social-accounts

Adds OAuth social login (via Laravel Socialite) to a Laravel app, letting users authenticate or link accounts through third-party providers; consumers use the `HasSocialAccounts` trait on their User model and optionally the `SocialAccounts` facade to configure provider-specific Socialite settings.

## Public API

- **`SocialAccounts` facade** (`src/Facades/SocialAccounts.php`) — backed by `src/SocialAccounts.php`. Used in application boot to register per-provider Socialite method calls via `SocialAccounts::registerProviderSettings(string $provider, string $methodName, ?array $parameters)`.
- **`HasSocialAccounts` trait** (`src/Traits/HasSocialAccounts.php`) — add to the User model. Provides `socialAccounts()` relation, `addSocialAccount()`, `findBySocialAccount()`, and `hasProvider()`.
- **`Strategy` interface** (`src/Interfaces/Strategy.php`) — implement to create custom login/link strategies. Three built-in strategies in `src/Strategies/`: `FindUser`, `FindOrCreateUser`, `AddSocialAccount`.
- **Events**: `SocialAccountAdded` and `SocialUserCreated` (both in `src/Events/`) are fired after a social account is linked or a new user is created.

## Service Provider

Config key: `social-accounts`. Published assets (via `php artisan vendor:publish`):

- **Config**: `config/social-accounts.php` — controls providers list, route prefix/middleware, `automatically_create_users`, model/table/column overrides, and gate names.
- **Migrations**: two stubs in `database/migrations/` — `create_social_accounts_table` and `make_email_and_password_nullable` (makes `email`/`password` nullable on the users table to support social-only accounts).
- **Routes**: web routes registered per-provider from the `providers` config array (e.g. `GET /social-accounts/login/google`, `GET /social-accounts/login/google/callback`); API resource route for `index`, `show`, `destroy` on `SocialAccount`.

Gate checks (`login-with-provider` and `add-social-account`) are optional — they are only applied if the gate is defined by the consuming app.

## Dependencies

- Internal: none
- External: `laravel/socialite ^5.0`

## Testing

```
composer test
```

Tests use Mockery to mock Socialite's driver, provider, and `ProviderUser` contracts. The base `TestCase` (`tests/TestCase.php`) runs both package migrations manually in `setUp()` and resets `SocialAccounts::emptyProviderSettings()` before/after each test. Test fixtures include `tests/Models/User.php` (uses `HasSocialAccounts`) and `tests/Factories/UserFactory.php`.

## Conventions / Gotchas

- **`automatically_create_users` defaults to `false`**: new users can only add social logins while already authenticated. Set to `true` in config (or override in tests) to allow registration via OAuth.
- **Model binding via config**: `SocialAccount` and the user model class, table names, and foreign/primary key column names are all resolved from config at runtime — override in `config/social-accounts.php` rather than subclassing.
- **Route registration is per-provider**: only providers listed in `config('social-accounts.providers')` get routes. Named routes follow the pattern `social-accounts.login.{provider}` and `social-accounts.callback.{provider}`.
- **Redirect URL auto-configuration**: `ProviderController` sets `services.{provider}.redirect` automatically if not already configured, using the named callback route.
- **`user_id_column` type config**: if your users table uses a non-bigInteger or signed primary key, set `user_id_column.type` and `user_id_column.unsigned` in config before running migrations.
