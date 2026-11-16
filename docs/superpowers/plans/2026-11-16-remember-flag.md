# Remember flag fix Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** The `remember` flag applies only to the login flow that set it, and a user without a password gets no remember cookie.

**Architecture:** Two changes in `src/Controllers/ProviderController.php`. `redirectToProvider()` puts or forgets `remember` on each flow. `handleProviderCallback()` pulls `remember` at the top (before the `error` check), and passes it to `Auth::login()` only when `is_string($user->getAuthPassword())`.

**Tech Stack:** PHP, Laravel 13, Orchestra Testbench, PHPUnit.

**Spec:** `docs/superpowers/specs/2026-11-16-remember-flag-design.md`

## Global Constraints

- The session key stays `remember`.
- The password check is `is_string($user->getAuthPassword())`, the same check as `SessionGuard::userFromRecaller()`.
- Do not change `HasSocialAccounts` or `getAuthPassword()`.
- The recaller cookie name in tests: `Auth::guard()->getRecallerName()`.

## Review Focus

- A user that `FindOrCreateUser` makes in the same callback has no password: expect no remember cookie, and the user is logged in (Task 2 test).
- The full issue flow (`?remember`, failed callback, then a new flow without `?remember`): expect no `remember` in the session after the second redirect (Task 1 test).
- A user with an empty string password (`''`): `is_string('')` is true, so the cookie is set. This matches Laravel, which accepts that cookie. No test; do not add a special case.
- `AddSocialAccount` with `remember` in the session: the flag is removed and no cookie is set (the existing test `test_user_is_not_logged_in_again_when_social_account_is_added` covers it; it must still pass).
- `?remember=0` or `?remember=false`: `has('remember')` is true, so the flag is set. This is the current behavior; do not change it.

---

### Task 1: Remove the flag after a failed or abandoned flow

**Files:**
- Modify: `src/Controllers/ProviderController.php` (`redirectToProvider()`, `handleProviderCallback()`)
- Test: `tests/Feature/ProviderTest.php`

**Interfaces:**
- Produces: in `handleProviderCallback()`, a local `$remember` (bool) that is pulled at the top. Task 2 uses it.

- [ ] **Step 1: Add the flag to the failure tests**

In each of these existing tests, add `'remember' => true` to the `withSession([...])` array (add a `withSession(['remember' => true])` call where there is none), and add `$response->assertSessionMissing('remember');` after the status assertion:

- `test_it_fails_to_log_in_when_automatically_create_users_is_false` (401)
- `test_it_fails_to_create_account_when_gate_only_allows_a_certain_email_address` (403)
- `test_it_fails_when_social_account_belongs_to_another_user` (`SocialAccountBelongsToAnotherUser`)
- `test_it_fails_to_add_a_second_social_account_with_the_same_provider` (409)
- `test_it_redirects_when_provider_returns_an_error` (`error`)

- [ ] **Step 2: Write the new tests**

```php
public function test_login_url_without_remember_removes_an_old_remember_flag()
{
    $this->mockSocialite();

    $response = $this
        ->withSession(['remember' => true])
        ->get("/{$this->prefix}/login/{$this->provider}");

    $response->assertSessionMissing('remember');
}

public function test_remember_from_an_abandoned_flow_is_not_used_by_the_next_flow()
{
    $this->mockSocialite();

    $this->get("/{$this->prefix}/login/{$this->provider}?remember")->assertSessionHas('remember', true);
    $response = $this->get("/{$this->prefix}/login/{$this->provider}");

    $response->assertSessionMissing('remember');
}
```

- [ ] **Step 3: Run the tests to see them fail**

Run: `composer test -- --filter=ProviderTest`
Expected: FAIL in the five changed tests and the two new tests, at `assertSessionMissing('remember')`.

- [ ] **Step 4: Change `ProviderController`**

- `redirectToProvider()`: when `$this->request->has('remember')`, put `remember` = `true`; else `forget('remember')`.
- `handleProviderCallback()`: move `$remember = $this->request->session()->pull('remember', false);` to the top, before the `social-accounts.redirect` pull. Update its comment to tell that the flag applies only to this callback.

- [ ] **Step 5: Run the tests to see them pass**

Run: `composer test`
Expected: PASS (all tests).

- [ ] **Step 6: Commit**

```bash
git add src/Controllers/ProviderController.php tests/Feature/ProviderTest.php
git commit -m "fix: remove the remember flag after a failed or abandoned login"
```

### Task 2: No remember cookie for a user without a password

**Files:**
- Modify: `src/Controllers/ProviderController.php` (`Auth::login()` call)
- Modify: `README.md` ("Remember Me" section), `CLAUDE.md` ("`ProviderController` flow")
- Test: `tests/Feature/ProviderTest.php`

**Interfaces:**
- Consumes: `$remember` from Task 1.

- [ ] **Step 1: Write the tests**

Use the setup of `test_it_logs_in_a_user` (existing user with a social account, `mockSocialite(...)`), with `->withSession(['remember' => true])` on the callback request.

- `test_it_sets_a_remember_cookie_for_a_user_with_a_password`: user from `User::factory()->create()`. Assert `$response->assertCookie(Auth::guard()->getRecallerName());` and `$this->assertAuthenticatedAs($user);`.
- `test_it_does_not_set_a_remember_cookie_for_a_user_without_a_password`: user from `User::factory()->create(['password' => null])`. Assert `assertCookieMissing(Auth::guard()->getRecallerName())`, `assertAuthenticatedAs($user)`, and `$this->assertNull($user->fresh()->remember_token);`.
- `test_it_does_not_set_a_remember_cookie_for_a_new_user`: `enableUserCreation()`, `mockSocialite()` with a new email, no user in the database. Assert `assertCookieMissing(...)`, `assertSessionMissing('remember')`, and `$this->assertTrue(Auth::check());`.

- [ ] **Step 2: Run the tests to see them fail**

Run: `composer test -- --filter=remember_cookie`
Expected: the "with a password" test PASSES (current behavior). The two "does not set" tests FAIL at `assertCookieMissing`.

- [ ] **Step 3: Change the login call**

`Auth::login($user, $remember && is_string($user->getAuthPassword()));`, with a one-line comment: Laravel does not accept a remember cookie for a user without a password.

- [ ] **Step 4: Update the README and CLAUDE.md**

After the example in "Remember Me", add: "Remember me" needs a user with a password. Laravel does not accept a remember cookie for a user without a password (for example a user that the package created), so the package logs in such a user without "remember me".

In `CLAUDE.md`, "`ProviderController` flow": tell that `redirectToProvider()` puts or removes `remember` on each flow, the callback pulls it before the first early return, and a user without a password is logged in without remember.

- [ ] **Step 5: Run the checks**

Run: `composer verify`
Expected: Pint, phpmd and PHPUnit all pass.

- [ ] **Step 6: Commit**

```bash
git add src/Controllers/ProviderController.php tests/Feature/ProviderTest.php README.md CLAUDE.md
git commit -m "fix: do not remember a user without a password"
```
