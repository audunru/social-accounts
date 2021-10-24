<?php

namespace Tests\Feature;

use audunru\SocialAccounts\Events\SocialAccountAdded;
use audunru\SocialAccounts\Events\SocialUserCreated;
use audunru\SocialAccounts\Models\SocialAccount;
use audunru\SocialAccounts\Tests\Models\User;
use audunru\SocialAccounts\Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Laravel\Socialite\Contracts\User as ProviderUser;

class ProviderTest extends TestCase
{
    use WithFaker;

    protected $provider = 'google';
    protected $prefix = 'social-accounts';
    protected $redirectTo = 'http://localhost';

    public function testItFailsToLogInWhenAutomaticallyCreateUsersIsFalse()
    {
        $this->disableUserCreation();

        $this->mockSocialite();

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");
        $response->assertStatus(401);
        $this->assertFalse(Auth::check());
    }

    public function testItFailsToCreateAccountWhenGateOnlyAllowsACertainEmailAddress()
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

    public function testItCreatesAccountWhenGateOnlyAllowsACertainEmailAddress()
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

    public function testItLogsInAUser()
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

    public function testSessionHasRememberIfItWasPresentInLoginUrl()
    {
        $user = factory(User::class)->create();
        $socialAccount = factory(SocialAccount::class)->make(['provider' => $this->provider]);
        $user->addSocialAccount($socialAccount);

        $this->mockSocialite($user->email, $user->name, $socialAccount->provider_user_id);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}?remember");

        $response->assertSessionHas('remember', true);
    }

    public function testItLogsInAUserWhileAutomaticallyCreateUsersIsOn()
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

    public function testUserIsAuthenticatedButLogsInAsSomeoneElse()
    {
        $this->enableSocialAccountCreation();

        $user = factory(User::class)->create();
        $socialAccount = factory(SocialAccount::class)->make(['provider' => $this->provider]);
        $user->addSocialAccount($socialAccount);

        $this->mockSocialite($user->email, $user->name, $socialAccount->provider_user_id);

        $anotherUser = factory(User::class)->create();

        $response = $this
            ->actingAs($anotherUser)
            ->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(302);
        $this->assertEquals($this->redirectTo, $response->getTargetUrl());
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
    }

    public function testItCreatesAUser()
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

    public function testItAddsASocialAccount()
    {
        $this->enableSocialAccountCreation();

        $user = factory(User::class)->create();

        $provider_user_id = $this->faker->uuid;
        $this->mockSocialite($user->email, $user->name, $provider_user_id);

        $response = $this
            ->actingAs($user)
            ->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(302);
        $this->assertEquals($this->redirectTo, $response->getTargetUrl());
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
        $this->assertDatabaseHas('social_accounts', [
            'provider'         => $this->provider,
            'provider_user_id' => $provider_user_id,
        ]);
    }

    public function testItFailsToAddSocialAccountWhenItIsDisabled()
    {
        $this->disableSocialAccountCreation();

        $user = factory(User::class)->create();

        $provider_user_id = $this->faker->uuid;
        $this->mockSocialite($user->email, $user->name, $provider_user_id);

        $response = $this
                ->actingAs($user)
                ->get("/{$this->prefix}/login/{$this->provider}/callback");
        $response
            ->assertStatus(403);
        $this->assertDatabaseMissing('social_accounts', [
            'provider'         => $this->provider,
            'provider_user_id' => $provider_user_id,
        ]);
    }

    public function testItFailsToAddASecondSocialAccountWithTheSameProvider()
    {
        $this->enableSocialAccountCreation();

        $user = factory(User::class)->create();
        $socialAccount = factory(SocialAccount::class)->make(['provider' => $this->provider]);
        $user->addSocialAccount($socialAccount);

        $provider_user_id = $this->faker->uuid;
        $this->mockSocialite($user->email, $user->name, $provider_user_id);

        $response = $this
            ->actingAs($user)
            ->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(409);
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
        $this->assertDatabaseMissing('social_accounts', [
            'provider'         => $this->provider,
            'provider_user_id' => $provider_user_id,
        ]);
    }

    public function testAnEventIsDispatchedWhenUserIsCreated()
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

    public function testAnEventIsDispatchedWhenSocialAccountIsAdded()
    {
        Event::fake();

        $this->enableSocialAccountCreation();

        $user = factory(User::class)->create();

        $provider_user_id = $this->faker->uuid;
        $this
            ->actingAs($user)
            ->mockSocialite($user->email, $user->name, $provider_user_id);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        Event::assertDispatched(SocialAccountAdded::class, function ($event) use ($user, $provider_user_id) {
            return $event->user->is($user) &&
                $event->socialAccount->user_id === $user->id &&
                $event->socialAccount->provider_user_id === $provider_user_id &&
                $event->providerUser->getId() === $provider_user_id;
        });
    }
}
