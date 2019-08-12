<?php

namespace Tests\Feature;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Event;
use audunru\SocialAccounts\Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use audunru\SocialAccounts\Tests\Models\User;
use audunru\SocialAccounts\Models\SocialAccount;
use audunru\SocialAccounts\Events\SocialUserCreated;
use audunru\SocialAccounts\Events\SocialAccountAdded;
use Laravel\Socialite\Contracts\User as ProviderUser;

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
        $this->requireLaravelVersion('5.7.0');
        $this->enableUserCreation();

        Gate::define('login-with-provider', function (?User $user, ProviderUser $providerUser) {
            return 'jerry@seinfeld.com' === $providerUser->getEmail();
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
        $this->requireLaravelVersion('5.7.0');

        $this->enableUserCreation();

        Gate::define('login-with-provider', function (?User $user, ProviderUser $providerUser) {
            return 'jerry@seinfeld.com' === $providerUser->getEmail();
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
        $user = factory(User::class)->create();
        $socialAccount = factory(SocialAccount::class)->make(['provider' => $this->provider]);
        $user->addSocialAccount($socialAccount);

        $this->mockSocialite($user->email, $user->name, $socialAccount->provider_user_id);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(302);
        $this->assertEquals($this->redirectTo, $response->getTargetUrl());
        $this->assertEquals($user->id, Auth::id());
    }

    public function test_session_has_remember_if_it_was_present_in_login_url()
    {
        $user = factory(User::class)->create();
        $socialAccount = factory(SocialAccount::class)->make(['provider' => $this->provider]);
        $user->addSocialAccount($socialAccount);

        $this->mockSocialite($user->email, $user->name, $socialAccount->provider_user_id);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}?remember");

        $response->assertSessionHas('remember', true);
    }

    public function test_it_logs_in_a_user_while_automatically_create_users_is_on()
    {
        $this->enableUserCreation();

        $user = factory(User::class)->create();
        $socialAccount = factory(SocialAccount::class)->make(['provider' => $this->provider]);
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

        $user = factory(User::class)->create();
        $socialAccount = factory(SocialAccount::class)->make(['provider' => $this->provider]);
        $user->addSocialAccount($socialAccount);

        $anotherUser = factory(User::class)->create();

        Auth::login($anotherUser);

        $this->mockSocialite($user->email, $user->name, $socialAccount->provider_user_id);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(302);
        $this->assertEquals($this->redirectTo, $response->getTargetUrl());
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
    }

    public function test_it_creates_a_user()
    {
        $this->enableUserCreation();

        $user = factory(User::class)->make();
        $this->mockSocialite($user->email, $user->name, $this->faker->uuid);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(302);
        $this->assertEquals($this->redirectTo, $response->getTargetUrl());
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->email, Auth::user()->email);
        $this->assertDatabaseHas('users', [
            'email' => $user->email,
            'name'  => $user->name,
        ]);
    }

    public function test_it_adds_a_social_account()
    {
        $this->enableSocialAccountCreation();

        $user = factory(User::class)->create();

        Auth::login($user);

        $provider_user_id = $this->faker->uuid;
        $this->mockSocialite($user->email, $user->name, $provider_user_id);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(302);
        $this->assertEquals($this->redirectTo, $response->getTargetUrl());
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
        $this->assertDatabaseHas('social_accounts', [
            'provider'         => $this->provider,
            'provider_user_id' => $provider_user_id,
        ]);
    }

    public function test_it_fails_to_add_social_account_when_it_is_disabled()
    {
        $this->disableSocialAccountCreation();

        $user = factory(User::class)->create();

        Auth::login($user);

        $provider_user_id = $this->faker->uuid;
        $this->mockSocialite($user->email, $user->name, $provider_user_id);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");
        $response
            ->assertStatus(403);
        $this->assertDatabaseMissing('social_accounts', [
            'provider'         => $this->provider,
            'provider_user_id' => $provider_user_id,
        ]);
    }

    public function test_it_fails_to_add_a_second_social_account_with_the_same_provider()
    {
        $this->enableSocialAccountCreation();

        $user = factory(User::class)->create();
        $socialAccount = factory(SocialAccount::class)->make(['provider' => $this->provider]);
        $user->addSocialAccount($socialAccount);

        Auth::login($user);

        $provider_user_id = $this->faker->uuid;
        $this->mockSocialite($user->email, $user->name, $provider_user_id);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(409);
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
        $this->assertDatabaseMissing('social_accounts', [
            'provider'         => $this->provider,
            'provider_user_id' => $provider_user_id,
        ]);
    }

    public function test_an_event_is_dispatched_when_user_is_created()
    {
        Event::fake();

        $this->enableUserCreation();

        $user = factory(User::class)->make();
        $provider_user_id = $this->faker->uuid;

        $this->mockSocialite($user->email, $user->name, $provider_user_id);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        Event::assertDispatched(SocialUserCreated::class, function ($event) use ($user, $provider_user_id) {
            return $event->user->name === $user->name &&
                $event->user->email === $user->email &&
                $event->socialAccount->provider_user_id === $provider_user_id &&
                $event->providerUser->getId() === $provider_user_id;
        });
    }

    public function test_an_event_is_dispatched_when_social_account_is_added()
    {
        Event::fake();

        $this->enableSocialAccountCreation();

        $user = factory(User::class)->create();

        Auth::login($user);

        $provider_user_id = $this->faker->uuid;
        $this->mockSocialite($user->email, $user->name, $provider_user_id);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        Event::assertDispatched(SocialAccountAdded::class, function ($event) use ($user, $provider_user_id) {
            return $event->user->is($user) &&
                $event->socialAccount->user_id === $user->id &&
                $event->socialAccount->provider_user_id === $provider_user_id &&
                $event->providerUser->getId() === $provider_user_id;
        });
    }
}
