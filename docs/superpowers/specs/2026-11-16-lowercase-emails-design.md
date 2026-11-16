# Lower case the provider email when a user is created

Issue: #133

## Intent

`createUser()` saves the email with the case that the provider sends. Apps that use Fortify with
`lowercase_usernames => true` store lower case emails for password users. On a case-sensitive
database, a lookup with `where('email', Str::lower($email))` does not find a user that the package
created with upper case letters. The stored data must be consistent.

## Goals

- A config setting `lowercase_emails`, default `true`.
- When it is `true` and the email is not null, `createUser()` saves `Str::lower($email)`.

## Non-goals

- No change to the existing-email check. It stays case-insensitive (`lower(email) = ?`), because
  other code in the app can save emails with upper case letters.
- No change to existing users. Users are found by provider id, so a login is not affected.
- No change to the `ProviderUser` that the gate and the events get.

## Approaches

Only one sensible way: change the local `$email` in `createUser()` before the check and the insert.
A model mutator or a Socialite wrapper would change more code and would also affect code outside the
package.

## Design

- `config/social-accounts.php`: add `'lowercase_emails' => true` after `automatically_create_users`,
  with a comment that it has the same function as `fortify.lowercase_usernames`.
- `src/Traits/FindsAndCreatesUsers.php`: before the existing-email check, when
  `config('social-accounts.lowercase_emails', true)` is true and `$email` is not null, set
  `$email = Str::lower($email)` (`mb_strtolower`, the same function as Fortify).
- README: describe the setting next to the text about automatic user creation, and tell apps that
  use Fortify to use the same value in the two settings.
- Tests: `true` with `User@Example.com` gives `user@example.com`; `false` keeps `User@Example.com`;
  a null email gives no error and stays null.

## Release

Breaking change: the default `true` changes the stored data, and the config is merged at the top
level, so old published configs also get `true`. The commit body must have a `BREAKING CHANGE:`
footer (the angular preset ignores `!`). Use a rebase merge, so the footer is not lost.
