# Remember flag only for the login flow that set it

Issue: #140

## Intent

`ProviderController::redirectToProvider()` puts `remember` in the session when the request has `?remember`. Only the
success path of `handleProviderCallback()` pulls it. After a failed callback (provider `error`, 403, 401, 409,
`SocialAccountBelongsToAnotherUser`) or a flow that the user abandons at the provider, the flag stays in the session.
A later login without `?remember` then sets a remember cookie that the user did not ask for.

The `remember` flag must apply only to the login flow that set it.

## Laravel 13 and users without a password

`SessionGuard::userFromRecaller()` (Laravel 13.39) returns no user when `getAuthPassword()` is not a string. Users that
`FindOrCreateUser` makes have no password. For these users, `Auth::login($user, true)` writes a `remember_token` and
sends a remember cookie, but Laravel never accepts that cookie.

## Goals

- A failed or abandoned flow does not change a later login.
- No remember cookie for a user without a password, because Laravel cannot use it.
- The README tells that "remember me" needs a user with a password.

## Non-goals

- Make "remember me" work for users without a password (for example, return `''` from `getAuthPassword()`). This
  removes a check that Laravel added for security.
- Rename the `remember` session key.

## Approaches

1. **Reset at the start of each flow, and pull at the start of the callback (picked).** `redirectToProvider()` puts
   `remember` when the request has `?remember`, and removes it when it does not. `handleProviderCallback()` pulls it
   before the first early return, as it already does with `social-accounts.redirect`. No stale flag stays in the session.
2. Reset in `redirectToProvider()` only. This stops the bug, but a stale flag stays in the session after a failed
   callback until the next flow starts.
3. Pull at the start of the callback only. This does not cover an abandoned flow, so it does not fix the bug.

## Design

- `redirectToProvider()`: if the request has `remember`, put `remember` = `true`; else forget `remember`.
- `handleProviderCallback()`: pull `remember` at the top, next to the pull of `social-accounts.redirect`, before the
  `error` check.
- Log in with `Auth::login($user, $remember && is_string($user->getAuthPassword()))`. This is the same check as Laravel
  uses in `userFromRecaller()`.
- README "Remember Me": tell that it needs a user with a password, and that the package logs in users without a password
  without "remember me".

## Tests (left to the builder)

Tests for: the flag is removed after each failed path; `redirectToProvider()` without `?remember` removes an old flag;
a user without a password gets no remember cookie; a user with a password still gets one.
