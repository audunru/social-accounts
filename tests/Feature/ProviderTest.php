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
use Orchestra\Testbench\Attributes\WithMigration;

/**
 * @SuppressWarnings("unused")
 */
#[WithMigration]
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
        $response->assertUnauthorized();
        $this->assertFalse(Auth::check());
    }

    public function testItFailsToCreateAccountWhenGateOnlyAllowsACertainEmailAddress()
    {
        $this->enableUserCreation();

        Gate::define('login-with-provider', function (?User $user, ProviderUser $providerUser) {
            return 'jerry@seinfeld.com' === $providerUser->getEmail();
        });

        $this->mockSocialite('newman@seinfeld.com');

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertForbidden();
        $this->assertFalse(Auth::check());
        $this->assertDatabaseMissing('users', [
            'email' => 'newman@seinfeld.com',
        ]);
    }

    public function testItCreatesAccountWhenGateOnlyAllowsACertainEmailAddress()
    {
        $this->enableUserCreation();

        Gate::define('login-with-provider', function (?User $user, ProviderUser $providerUser) {
            return 'jerry@seinfeld.com' === $providerUser->getEmail();
        });

        $this->mockSocialite('jerry@seinfeld.com');

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertFound();
        $this->assertTrue(Auth::check());
        $this->assertDatabaseHas('users', [
            'email' => 'jerry@seinfeld.com',
        ]);
    }

    public function testItLogsInAUser()
    {
        $user = User::factory()->create();
        $socialAccount = SocialAccount::factory()->make(['provider' => $this->provider]);
        $user->addSocialAccount($socialAccount);

        $this->mockSocialite($user->email, $user->name, $socialAccount->provider_user_id);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertFound();
        $this->assertEquals($this->redirectTo, $response->getTargetUrl());
        $this->assertEquals($user->id, Auth::id());
    }

    public function testSessionHasRememberIfItWasPresentInLoginUrl()
    {
        $user = User::factory()->create();
        $socialAccount = SocialAccount::factory()->make(['provider' => $this->provider]);
        $user->addSocialAccount($socialAccount);

        $this->mockSocialite($user->email, $user->name, $socialAccount->provider_user_id);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}?remember");

        $response->assertSessionHas('remember', true);
    }

    public function testItLogsInAUserWhileAutomaticallyCreateUsersIsOn()
    {
        $this->enableUserCreation();

        $user = User::factory()->create();
        $socialAccount = SocialAccount::factory()->make(['provider' => $this->provider]);
        $user->addSocialAccount($socialAccount);

        $this->mockSocialite($user->email, $user->name, $socialAccount->provider_user_id);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertFound();
        $this->assertEquals($this->redirectTo, $response->getTargetUrl());
        $this->assertEquals($user->id, Auth::id());
    }

    public function testUserIsAuthenticatedButLogsInAsSomeoneElse()
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

        $response->assertFound();
        $this->assertEquals($this->redirectTo, $response->getTargetUrl());
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
    }

    public function testItCreatesAUser()
    {
        $this->enableUserCreation();

        $user = User::factory()->make();
        $this->mockSocialite($user->email, $user->name, $this->faker->uuid);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertFound();
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

        $user = User::factory()->create();

        $providerUserId = $this->faker->uuid;
        $this->mockSocialite($user->email, $user->name, $providerUserId);

        $response = $this
            ->actingAs($user)
            ->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertFound();
        $this->assertEquals($this->redirectTo, $response->getTargetUrl());
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
        $this->assertDatabaseHas('social_accounts', [
            'provider'         => $this->provider,
            'provider_user_id' => $providerUserId,
        ]);
    }

    public function testItFailsToAddSocialAccountWhenItIsDisabled()
    {
        $this->disableSocialAccountCreation();

        $user = User::factory()->create();

        $providerUserId = $this->faker->uuid;
        $this->mockSocialite($user->email, $user->name, $providerUserId);

        $response = $this
                ->actingAs($user)
                ->get("/{$this->prefix}/login/{$this->provider}/callback");
        $response
            ->assertForbidden();
        $this->assertDatabaseMissing('social_accounts', [
            'provider'         => $this->provider,
            'provider_user_id' => $providerUserId,
        ]);
    }

    public function testItFailsToAddASecondSocialAccountWithTheSameProvider()
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

        $response->assertConflict();
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
        $this->assertDatabaseMissing('social_accounts', [
            'provider'         => $this->provider,
            'provider_user_id' => $providerUserId,
        ]);
    }

    public function testAnEventIsDispatchedWhenUserIsCreated()
    {
        Event::fake();

        $this->enableUserCreation();

        $user = User::factory()->make();
        $providerUserId = $this->faker->uuid;

        $this->mockSocialite($user->email, $user->name, $providerUserId);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        Event::assertDispatched(SocialUserCreated::class, function ($event) use ($user, $providerUserId) {
            return $event->user->name === $user->name
                && $event->user->email === $user->email
                && $event->socialAccount->provider_user_id === $providerUserId
                && $event->providerUser->getId() === $providerUserId;
        });
    }

    public function testAnEventIsDispatchedWhenSocialAccountIsAdded()
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
