<?php

namespace Tests\Feature;

use audunru\SocialAccounts\Exceptions\EmailAlreadyRegistered;
use audunru\SocialAccounts\Exceptions\UserCreationDenied;
use audunru\SocialAccounts\Models\SocialAccount;
use audunru\SocialAccounts\Tests\Models\User;
use audunru\SocialAccounts\Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Laravel\Socialite\Contracts\User as ProviderUser;

/**
 * @SuppressWarnings("unused")
 */
class CreateUserGateTest extends TestCase
{
    use WithFaker;

    protected $provider = 'google';

    protected $prefix = 'social-accounts';

    public function test_it_fails_to_create_a_user_when_gate_denies()
    {
        $this->enableUserCreation();
        $this->defineCreateUserGate(false);

        $this->mockSocialite('user@example.com', 'User', $this->faker->uuid);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(403);
        $this->assertInstanceOf(UserCreationDenied::class, $response->exception);
        $this->assertFalse(Auth::check());
        $this->assertDatabaseCount('users', 0);
        $this->assertDatabaseCount('social_accounts', 0);
    }

    public function test_it_creates_a_user_when_gate_allows()
    {
        $this->enableUserCreation();
        $this->defineCreateUserGate(true);

        $this->mockSocialite('user@example.com', 'User', $this->faker->uuid);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(302);
        $this->assertTrue(Auth::check());
        $this->assertDatabaseHas('users', ['email' => 'user@example.com']);
    }

    public function test_gate_does_not_apply_when_a_linked_user_logs_in()
    {
        $this->enableUserCreation();
        $this->defineCreateUserGate(false);

        $user = User::factory()->create();
        $socialAccount = SocialAccount::factory()->make(['provider' => $this->provider]);
        $user->addSocialAccount($socialAccount);

        $this->mockSocialite($user->email, $user->name, $socialAccount->provider_user_id);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(302);
        $this->assertEquals($user->id, Auth::id());
    }

    public function test_gate_runs_before_the_email_check()
    {
        $this->enableUserCreation();
        $this->defineCreateUserGate(false);

        User::factory()->create(['email' => 'user@example.com']);

        $this->mockSocialite('user@example.com', 'Another User', $this->faker->uuid);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(403);
        $this->assertInstanceOf(UserCreationDenied::class, $response->exception);
        $this->assertNotInstanceOf(EmailAlreadyRegistered::class, $response->exception);
    }

    public function test_gate_is_used_when_published_config_does_not_name_it()
    {
        $this->enableUserCreation();
        config(['social-accounts.gates' => [
            'login-with-provider' => 'login-with-provider',
            'add-social-account' => 'add-social-account',
        ]]);
        $this->defineCreateUserGate(false);

        $this->mockSocialite('user@example.com', 'User', $this->faker->uuid);

        $response = $this->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(403);
        $this->assertInstanceOf(UserCreationDenied::class, $response->exception);
        $this->assertDatabaseCount('users', 0);
    }

    private function defineCreateUserGate(bool $allow): void
    {
        Gate::define('create-user-with-provider', function (?User $user, ProviderUser $providerUser) use ($allow) {
            return $allow;
        });
    }
}
