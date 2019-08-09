<?php

namespace Tests\Feature;

use audunru\SocialAccounts\Models\SocialAccount;
use audunru\SocialAccounts\Tests\Models\User;
use audunru\SocialAccounts\Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Mockery;

class ProviderTest extends TestCase
{
    use WithFaker;

    protected $provider = 'google';
    protected $prefix = 'social-accounts';
    protected $redirectTo = 'http://localhost';

    public function test_it_fails_to_log_in_when_automatically_create_users_is_false()
    {
        $this->disableUserCreation();

        $this->mockSocialiteCallback();

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(401);
        $this->assertFalse(Auth::check());
    }

    public function test_it_logs_in_a_user()
    {
        $user = factory(User::class)->create();
        $socialAccount = factory(SocialAccount::class)->make(['provider' => $this->provider]);
        $user->addSocialAccount($socialAccount);

        $this->mockSocialiteCallback($user->email, $user->name, $socialAccount->provider_user_id);

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

        $this->mockSocialiteCallback($anotherUser->email, $anotherUser->name, $socialAccount->provider_user_id);

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
        $this->mockSocialiteCallback($user->email, $user->name, $this->faker->uuid);

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

        $user = factory(User::class)->create();

        Auth::login($user);

        $provider_user_id = $this->faker->uuid;
        $this->mockSocialiteCallback($user->email, $user->name, $provider_user_id);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(302);
        $this->assertEquals($this->redirectTo, $response->getTargetUrl());
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
        $this->assertDatabaseHas('social_accounts', [
            'provider' => $this->provider,
            'provider_user_id' => $provider_user_id,
        ]);
    }

    public function test_it_fails_to_add_social_account_when_it_is_disabled()
    {
        $this->disableSocialAccountCreation();

        $user = factory(User::class)->create();

        Auth::login($user);

        $provider_user_id = $this->faker->uuid;
        $this->mockSocialiteCallback($user->email, $user->name, $provider_user_id);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");
        $response
            ->assertStatus(403);
        $this->assertDatabaseMissing('social_accounts', [
            'provider' => $this->provider,
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
        $this->mockSocialiteCallback($user->email, $user->name, $provider_user_id);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(409);
        $this->assertTrue(Auth::check());
        $this->assertEquals($user->id, Auth::id());
        $this->assertDatabaseMissing('social_accounts', [
            'provider' => $this->provider,
            'provider_user_id' => $provider_user_id,
        ]);
    }

    private function mockSocialiteCallback(string $email = 'art@vandelayindustries.com', string $name = 'Art Vandelay', string $provider_user_id = 'amazing-id')
    {
        // Mock a user which the provider will return
        $providerUser = Mockery::mock('Laravel\Socialite\Contracts\User');

        $providerUser
            ->shouldReceive('getEmail')
            ->andReturn($email)
            ->shouldReceive('getName')
            ->andReturn($name)
            ->shouldReceive('getId')
            ->andReturn($provider_user_id);

        // Mock a provider which Socialite will return
        $provider = Mockery::mock('Laravel\Socialite\Contracts\Provider');
        $provider
            ->shouldReceive('user')
            ->andReturn($providerUser);

        // Mock Socialite
        Socialite::shouldReceive('driver')
            ->andReturn($provider);
    }
}
