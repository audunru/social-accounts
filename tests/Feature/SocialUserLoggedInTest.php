<?php

namespace Tests\Feature;

use audunru\SocialAccounts\Events\SocialAccountAdded;
use audunru\SocialAccounts\Events\SocialUserLoggedIn;
use audunru\SocialAccounts\Models\SocialAccount;
use audunru\SocialAccounts\Tests\Models\User;
use audunru\SocialAccounts\Tests\TestCase;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Laravel\Socialite\Contracts\User as ProviderUser;
use Laravel\Socialite\Facades\Socialite;

/**
 * @SuppressWarnings("unused")
 */
class SocialUserLoggedInTest extends TestCase
{
    use WithFaker;

    protected $provider = 'google';

    protected $prefix = 'social-accounts';

    public function test_logged_in_event_is_dispatched_when_existing_user_logs_in()
    {
        Event::fake([SocialUserLoggedIn::class]);

        $this->disableUserCreation();

        $user = User::factory()->create();
        $socialAccount = SocialAccount::factory()->make(['provider' => $this->provider]);
        $user->addSocialAccount($socialAccount);

        $this->mockSocialite($user->email, $user->name, $socialAccount->provider_user_id);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(302);
        Event::assertDispatched(SocialUserLoggedIn::class, function ($event) use ($user, $socialAccount) {
            return $event->user->is($user)
                && $event->provider === $this->provider
                && $event->providerUser->getId() === $socialAccount->provider_user_id;
        });
    }

    public function test_logged_in_event_is_dispatched_when_existing_user_logs_in_while_automatically_create_users_is_on()
    {
        Event::fake([SocialUserLoggedIn::class]);

        $this->enableUserCreation();

        $user = User::factory()->create();
        $socialAccount = SocialAccount::factory()->make(['provider' => $this->provider]);
        $user->addSocialAccount($socialAccount);

        $this->mockSocialite($user->email, $user->name, $socialAccount->provider_user_id);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(302);
        Event::assertDispatched(SocialUserLoggedIn::class, function ($event) use ($user, $socialAccount) {
            return $event->user->is($user)
                && $event->provider === $this->provider
                && $event->providerUser->getId() === $socialAccount->provider_user_id;
        });
    }

    public function test_logged_in_event_is_dispatched_when_user_is_created()
    {
        Event::fake([SocialUserLoggedIn::class]);

        $this->enableUserCreation();

        $user = User::factory()->make();
        $providerUserId = $this->faker->uuid;
        $this->mockSocialite($user->email, $user->name, $providerUserId);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(302);
        Event::assertDispatched(SocialUserLoggedIn::class, function ($event) use ($user, $providerUserId) {
            return $event->user->email === $user->email
                && $event->provider === $this->provider
                && $event->providerUser->getId() === $providerUserId;
        });
    }

    public function test_logged_in_event_is_dispatched_when_authenticated_user_logs_in_again()
    {
        Event::fake([SocialUserLoggedIn::class]);

        $this->enableSocialAccountCreation();

        $user = User::factory()->create();
        $socialAccount = SocialAccount::factory()->make(['provider' => $this->provider]);
        $user->addSocialAccount($socialAccount);

        $this->mockSocialite($user->email, $user->name, $socialAccount->provider_user_id);

        $response = $this
            ->actingAs($user)
            ->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(302);
        Event::assertDispatched(SocialUserLoggedIn::class, function ($event) use ($user, $socialAccount) {
            return $event->user->is($user)
                && $event->provider === $this->provider
                && $event->providerUser->getId() === $socialAccount->provider_user_id;
        });
    }

    public function test_logged_in_event_is_not_dispatched_when_social_account_belongs_to_another_user()
    {
        Event::fake([SocialUserLoggedIn::class]);

        $this->enableSocialAccountCreation();

        $user = User::factory()->create();
        $socialAccount = SocialAccount::factory()->make(['provider' => $this->provider]);
        $user->addSocialAccount($socialAccount);

        $this->mockSocialite($user->email, $user->name, $socialAccount->provider_user_id);

        $anotherUser = User::factory()->create();

        $response = $this
            ->actingAs($anotherUser)
            ->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(409);
        Event::assertNotDispatched(SocialUserLoggedIn::class);
    }

    public function test_logged_in_event_is_not_dispatched_when_social_account_is_added()
    {
        Event::fake([SocialUserLoggedIn::class, SocialAccountAdded::class]);

        $this->enableSocialAccountCreation();

        $user = User::factory()->create();

        $providerUserId = $this->faker->uuid;
        $this->mockSocialite($user->email, $user->name, $providerUserId);

        $response = $this
            ->actingAs($user)
            ->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(302);
        Event::assertNotDispatched(SocialUserLoggedIn::class);
        Event::assertDispatched(SocialAccountAdded::class, function ($event) use ($user, $providerUserId) {
            return $event->user->is($user)
                && $event->socialAccount->provider === $this->provider
                && $event->providerUser->getId() === $providerUserId;
        });
    }

    public function test_user_is_not_logged_in_again_when_social_account_is_added()
    {
        Event::fake([Login::class]);

        $this->enableSocialAccountCreation();

        $user = User::factory()->create();

        $this->mockSocialite($user->email, $user->name, $this->faker->uuid);

        $response = $this
            ->actingAs($user)
            ->withSession(['remember' => true])
            ->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(302);
        $response->assertSessionMissing('remember');
        $this->assertAuthenticatedAs($user);
        Event::assertNotDispatched(Login::class);
    }

    public function test_logged_in_event_is_not_dispatched_when_provider_returns_an_error()
    {
        Event::fake([SocialUserLoggedIn::class]);

        $this->enableUserCreation();

        Socialite::shouldReceive('driver')->never();

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback?error=access_denied&state=abc");

        $response->assertStatus(302);
        Event::assertNotDispatched(SocialUserLoggedIn::class);
    }

    public function test_logged_in_event_is_not_dispatched_when_provider_returns_an_error_to_authenticated_user()
    {
        Event::fake([SocialUserLoggedIn::class]);

        $this->enableSocialAccountCreation();

        $user = User::factory()->create();

        Socialite::shouldReceive('driver')->never();

        $response = $this
            ->actingAs($user)
            ->get("/{$this->prefix}/login/{$this->provider}/callback?error=access_denied&state=abc");

        $response->assertStatus(302);
        Event::assertNotDispatched(SocialUserLoggedIn::class);
    }

    public function test_logged_in_event_is_not_dispatched_when_user_already_has_the_provider()
    {
        Event::fake([SocialUserLoggedIn::class]);

        $this->enableSocialAccountCreation();

        $user = User::factory()->create();
        $socialAccount = SocialAccount::factory()->make(['provider' => $this->provider]);
        $user->addSocialAccount($socialAccount);

        $this->mockSocialite($user->email, $user->name, $this->faker->uuid);

        $response = $this
            ->actingAs($user)
            ->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(409);
        Event::assertNotDispatched(SocialUserLoggedIn::class);
    }

    public function test_logged_in_event_is_not_dispatched_when_login_with_provider_gate_denies()
    {
        Event::fake([SocialUserLoggedIn::class]);

        $this->enableUserCreation();

        Gate::define('login-with-provider', function (?User $user, ProviderUser $providerUser) {
            return $providerUser->getEmail() === 'jerry@seinfeld.com';
        });

        $this->mockSocialite('newman@seinfeld.com');

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(403);
        Event::assertNotDispatched(SocialUserLoggedIn::class);
    }

    public function test_logged_in_event_is_not_dispatched_when_add_social_account_gate_denies()
    {
        Event::fake([SocialUserLoggedIn::class]);

        $this->disableSocialAccountCreation();

        $user = User::factory()->create();

        $this->mockSocialite($user->email, $user->name, $this->faker->uuid);

        $response = $this
            ->actingAs($user)
            ->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(403);
        Event::assertNotDispatched(SocialUserLoggedIn::class);
    }

    public function test_logged_in_event_is_not_dispatched_when_no_user_is_found()
    {
        Event::fake([SocialUserLoggedIn::class]);

        $this->disableUserCreation();

        $this->mockSocialite();

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(401);
        Event::assertNotDispatched(SocialUserLoggedIn::class);
    }

    public function test_listener_can_write_to_the_session_after_login()
    {
        Event::listen(SocialUserLoggedIn::class, function () {
            session()->put('logged_in_as', Auth::id());
        });

        $this->enableSocialAccountCreation();

        $user = User::factory()->create();
        $socialAccount = SocialAccount::factory()->make(['provider' => $this->provider]);
        $user->addSocialAccount($socialAccount);

        $this->mockSocialite($user->email, $user->name, $socialAccount->provider_user_id);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(302);
        $response->assertSessionHas('logged_in_as', $user->id);
    }
}
