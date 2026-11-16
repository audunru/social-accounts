# Lower case provider emails Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** A config setting `lowercase_emails` (default `true`) makes `createUser()` save the provider email in lower case.

**Architecture:** One change in `createUser()` in `src/Traits/FindsAndCreatesUsers.php`: lower case the local `$email` before the existing-email check and the insert. The `ProviderUser` that the gate and the events get is not changed.

**Tech Stack:** PHP, Laravel, Orchestra Testbench, PHPUnit.

**Spec:** `docs/superpowers/specs/2026-11-16-lowercase-emails-design.md`

## Global Constraints

- Config key: `social-accounts.lowercase_emails`, default `true`.
- Use `Str::lower()` (`mb_strtolower`), the same function as Fortify.
- The existing-email check stays `whereRaw('lower(email) = ?', [Str::lower($email)])`.
- Commit body must have a `BREAKING CHANGE:` footer. Merge the PR with rebase, not squash.

## Review Focus

- Non-ASCII letters (`ÆØÅ@Example.com`): expect `æøå@example.com` (multibyte lower case, not `strtolower`).
- An old published config without the key: expect `true` (the `config()` default is `true`).
- The gate and `SocialUserCreated` get the original `ProviderUser`: `$providerUser->getEmail()` keeps its case.
- A login of an existing user (found by provider id) does not change the stored email.
- Null email with the setting `true`: no error, the email stays null.

---

### Task 1: `lowercase_emails` setting

**Files:**
- Modify: `config/social-accounts.php` (after `automatically_create_users`)
- Modify: `src/Traits/FindsAndCreatesUsers.php` (`createUser()`, after `$email = $providerUser->getEmail();`)
- Modify: `README.md` (near the paragraph "The new user gets the email address that the provider returns", line 140)
- Test: `tests/Feature/CreateUserTest.php`

**Interfaces:**
- Consumes: the helpers `enableUserCreation()` and `mockSocialite($email, $name, $id)` in `tests/TestCase.php`.
- Produces: config key `social-accounts.lowercase_emails` (bool).

- [ ] **Step 1: Write the failing tests** in `CreateUserTest`. Each test calls `$this->enableUserCreation()`, mocks Socialite, calls `GET /social-accounts/login/google/callback` and asserts status 302.

```php
public function test_it_saves_the_email_in_lower_case()
// mockSocialite('User@Example.com', ...) → assertDatabaseHas('users', ['email' => 'user@example.com'])

public function test_it_saves_non_ascii_letters_in_lower_case()
// mockSocialite('ÆØÅ@Example.com', ...) → assertDatabaseHas('users', ['email' => 'æøå@example.com'])

public function test_it_keeps_the_email_case_when_lowercase_emails_is_false()
// config(['social-accounts.lowercase_emails' => false]); mockSocialite('User@Example.com', ...)
// → assertDatabaseHas('users', ['email' => 'User@Example.com'])

public function test_it_saves_the_email_in_lower_case_when_published_config_has_no_setting()
// config()->offsetUnset('social-accounts.lowercase_emails'); mockSocialite('User@Example.com', ...)
// → assertDatabaseHas('users', ['email' => 'user@example.com'])

public function test_it_creates_a_user_without_email_when_lowercase_emails_is_true()
// mockSocialite(null, ...) → assertDatabaseHas('users', ['email' => null]); assertDatabaseCount('users', 1)

public function test_the_event_gets_the_original_provider_email()
// Event::fake([SocialUserCreated::class]); mockSocialite('User@Example.com', ...)
// → Event::assertDispatched(SocialUserCreated::class, fn ($e) => $e->providerUser->getEmail() === 'User@Example.com')

public function test_it_does_not_change_the_email_of_an_existing_user_on_login()
// $user = User::factory()->create(['email' => 'User@Example.com']); link a SocialAccount for 'google'
// (pattern: test_it_logs_in_a_user in tests/Feature/ProviderTest.php);
// mockSocialite('User@Example.com', $user->name, $socialAccount->provider_user_id)
// → assertEquals($user->id, Auth::id()); assertDatabaseHas('users', ['email' => 'User@Example.com'])
```

The `ProviderUser` is the public property `$providerUser` on `SocialUserCreated` (from `SocialAccountAdded`). The SQLite `=` comparison is case-sensitive, so `assertDatabaseHas` checks the case.

- [ ] **Step 2: Run the tests to verify they fail**

Run: `composer test -- --filter=CreateUserTest`
Expected: FAIL in the three lower-case tests (stored email is `User@Example.com` / `ÆØÅ@Example.com`). The null-email, event and existing-user tests pass already.

- [ ] **Step 3: Implement**

- `createUser()`: when `! is_null($email) && config('social-accounts.lowercase_emails', true)`, set `$email = Str::lower($email)`. Put it before the existing-email check. Do not change the check.
- `config/social-accounts.php`: add `'lowercase_emails' => true,` with a comment in the style of the other entries: the email from the provider is saved in lower case when a new user is created; it has the same function as `lowercase_usernames` in Fortify; set the two settings to the same value.
- `README.md`: after the paragraph at line 140, add a paragraph: by default the email is saved in lower case (`lowercase_emails`); set it to `false` to keep the case from the provider; apps that use Laravel Fortify should use the same value as `fortify.lowercase_usernames`; existing users are not changed. Write it in the same style as the README.

- [ ] **Step 4: Run the tests to verify they pass**

Run: `composer test -- --filter=CreateUserTest`
Expected: PASS.

- [ ] **Step 5: Run the full checks**

Run: `composer fix && composer verify`
Expected: Pint with no changes left, phpmd with no violations, all PHPUnit tests pass.

- [ ] **Step 6: Commit**

```bash
git add config/social-accounts.php src/Traits/FindsAndCreatesUsers.php README.md tests/Feature/CreateUserTest.php
git commit -m "feat: lower case the provider email when a user is created" -m "Closes #133" -m "BREAKING CHANGE: new users get the provider email in lower case by default. Set social-accounts.lowercase_emails to false to keep the case from the provider."
```
