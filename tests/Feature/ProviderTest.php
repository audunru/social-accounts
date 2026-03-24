<?php

namespace Tests\Feature;

use audunru\SocialAccounts\Events\SocialAccountAdded;
use audunru\SocialAccounts\Events\SocialUserCreated;
use audunru\SocialAccounts\Models\SocialAccount;
use audunru\SocialAccounts\Tests\Models\User;
use audunru\SocialAccounts\Tests\TestCase;
use Illuminate\Auth\Events\Registered;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Laravel\Socialite\Contracts\User as ProviderUser;

/**
 * @SuppressWarnings("unused")
 */
class ProviderTest extends TestCase
{
    use WithFaker;

    protected $provider = 'google';

    protected $prefix = 'social-accounts';

    protected $redirectTo = 'http://localhost';

    public function test_it_fails_to_log_in_when_automatically_create_users_is_false()
    {
        $this->disableUserCreation();

        $this->mockSocialite();

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");
        $response->assertStatus(401);
        $this->assertFalse(Auth::check());
    }

    public function test_it_fails_to_create_account_when_gate_only_allows_a_certain_email_address()
    {
        $this->enableUserCreation();

        Gate::define('login-with-provider', function (?User $user, ProviderUser $providerUser) {
            return $providerUser->getEmail() === 'jerry@seinfeld.com';
        });

        $this->mockSocialite('newman@seinfeld.com');

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(403);
        $this->assertFalse(Auth::check());
        $this->assertDatabaseMissing('users', [
            'email' => 'newman@seinfeld.com',
        ]);
    }

    public function test_it_creates_account_when_gate_only_allows_a_certain_email_address()
    {
        $this->enableUserCreation();

        Gate::define('login-with-provider', function (?User $user, ProviderUser $providerUser) {
            return $providerUser->getEmail() === 'jerry@seinfeld.com';
        });

        $this->mockSocialite('jerry@seinfeld.com');

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(302);
        $this->assertTrue(Auth::check());
        $this->assertDatabaseHas('users', [
            'email' => 'jerry@seinfeld.com',
        ]);
    }

    public function test_it_logs_in_a_user()
    {
        $user = User::factory()->create();
        $socialAccount = SocialAccount::factory()->make(['provider' => $this->provider]);
        $user->addSocialAccount($socialAccount);

        $this->mockSocialite($user->email, $user->name, $socialAccount->provider_user_id);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(302);
        $this->assertEquals($this->redirectTo, $response->getTargetUrl());
        $this->assertEquals($user->id, Auth::id());
    }

    public function test_session_has_remember_if_it_was_present_in_login_url()
    {
        $user = User::factory()->create();
        $socialAccount = SocialAccount::factory()->make(['provider' => $this->provider]);
        $user->addSocialAccount($socialAccount);

        $this->mockSocialite($user->email, $user->name, $socialAccount->provider_user_id);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}?remember");

        $response->assertSessionHas('remember', true);
    }

    public function test_session_has_intended_url_if_it_was_present_in_login_url()
    {
        $user = User::factory()->create();
        $socialAccount = SocialAccount::factory()->make(['provider' => $this->provider]);
        $user->addSocialAccount($socialAccount);

        $this->mockSocialite($user->email, $user->name, $socialAccount->provider_user_id);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}?redirect=/custom-url");

        $response->assertSessionHas('url.intended', '/custom-url');
    }

    public function test_redirect_url_that_starts_with_double_slash_is_not_stored()
    {
        $user = User::factory()->create();
        $socialAccount = SocialAccount::factory()->make(['provider' => $this->provider]);
        $user->addSocialAccount($socialAccount);

        $this->mockSocialite($user->email, $user->name, $socialAccount->provider_user_id);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}?redirect=//evil.com");

        $response->assertSessionMissing('url.intended');
    }

    public function test_it_logs_in_a_user_while_automatically_create_users_is_on()
    {
        $this->enableUserCreation();

        $user = User::factory()->create();
        $socialAccount = SocialAccount::factory()->make(['provider' => $this->provider]);
        $user->addSocialAccount($socialAccount);

        $this->mockSocialite($user->email, $user->name, $socialAccount->provider_user_id);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(302);
        $this->assertEquals($this->redirectTo, $response->getTargetUrl());
        $this->assertEquals($user->id, Auth::id());
    }

    public function test_user_is_authenticated_but_logs_in_as_someone_else()
    {
        $this->enableSocialAccountCreation();

        $user = User::factory()->create();
        $socialAccount = SocialAccount::factory()->make(['provider' => $this->provider]);
        $user->addSocialAccount($socialAccount);

        $this->mockSocialite($user->email, $user->name, $socialAccount->provider_user_id);

        $anotherUser = User::factory()->create();

        $response = $this
            ->actingAs($anotherUser)
            ->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(302);
        $this->assertEquals($this->redirectTo, $response->getTargetUrl());
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
    }

    public function test_it_creates_a_user()
    {
        $this->enableUserCreation();

        $user = User::factory()->make();
        $this->mockSocialite($user->email, $user->name, $this->faker->uuid);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(302);
        $this->assertEquals($this->redirectTo, $response->getTargetUrl());
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->email, Auth::user()->email);
        $this->assertDatabaseHas('users', [
            'email' => $user->email,
            'name' => $user->name,
        ]);
    }

    public function test_it_adds_a_social_account()
    {
        $this->enableSocialAccountCreation();

        $user = User::factory()->create();

        $providerUserId = $this->faker->uuid;
        $this->mockSocialite($user->email, $user->name, $providerUserId);

        $response = $this
            ->actingAs($user)
            ->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(302);
        $this->assertEquals($this->redirectTo, $response->getTargetUrl());
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
        $this->assertDatabaseHas('social_accounts', [
            'provider' => $this->provider,
            'provider_user_id' => $providerUserId,
        ]);
    }

    public function test_it_fails_to_add_social_account_when_it_is_disabled()
    {
        $this->disableSocialAccountCreation();

        $user = User::factory()->create();

        $providerUserId = $this->faker->uuid;
        $this->mockSocialite($user->email, $user->name, $providerUserId);

        $response = $this
            ->actingAs($user)
            ->get("/{$this->prefix}/login/{$this->provider}/callback");
        $response
            ->assertStatus(403);
        $this->assertDatabaseMissing('social_accounts', [
            'provider' => $this->provider,
            'provider_user_id' => $providerUserId,
        ]);
    }

    public function test_it_fails_to_add_a_second_social_account_with_the_same_provider()
    {
        $this->enableSocialAccountCreation();

        $user = User::factory()->create();
        $socialAccount = SocialAccount::factory()->make(['provider' => $this->provider]);
        $user->addSocialAccount($socialAccount);

        $providerUserId = $this->faker->uuid;
        $this->mockSocialite($user->email, $user->name, $providerUserId);

        $response = $this
            ->actingAs($user)
            ->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(409);
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
        $this->assertDatabaseMissing('social_accounts', [
            'provider' => $this->provider,
            'provider_user_id' => $providerUserId,
        ]);
    }

    public function test_events_are_dispatched_when_user_is_created()
    {
        Event::fake();

        $this->enableUserCreation();

        $user = User::factory()->make();
        $providerUserId = $this->faker->uuid;

        $this->mockSocialite($user->email, $user->name, $providerUserId);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        Event::assertDispatched(Registered::class, function ($event) use ($user) {
            return $event->user->name === $user->name
                && $event->user->email === $user->email;
        });
        Event::assertDispatched(SocialUserCreated::class, function ($event) use ($user, $providerUserId) {
            return $event->user->name === $user->name
                && $event->user->email === $user->email
                && $event->socialAccount->provider_user_id === $providerUserId
                && $event->providerUser->getId() === $providerUserId;
        });
    }

    public function test_an_event_is_dispatched_when_social_account_is_added()
    {
        Event::fake();

        $this->enableSocialAccountCreation();

        $user = User::factory()->create();

        $providerUserId = $this->faker->uuid;
        $this
            ->actingAs($user)
            ->mockSocialite($user->email, $user->name, $providerUserId);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        Event::assertDispatched(SocialAccountAdded::class, function ($event) use ($user, $providerUserId) {
            return $event->user->is($user)
                && $event->socialAccount->user_id === $user->id
                && $event->socialAccount->provider_user_id === $providerUserId
                && $event->providerUser->getId() === $providerUserId;
        });
    }
}
