<?php

namespace Tests\Feature;

use audunru\SocialAccounts\Models\SocialAccount;
use audunru\SocialAccounts\Tests\Models\User;
use audunru\SocialAccounts\Tests\TestCase;
use Illuminate\Support\Facades\Auth;

class RememberTest extends TestCase
{
    protected $provider = 'google';

    protected $prefix = 'social-accounts';

    public function test_login_url_without_remember_removes_an_old_remember_flag()
    {
        $this->mockSocialite();

        $response = $this
            ->withSession(['remember' => true])
            ->get("/{$this->prefix}/login/{$this->provider}");

        $response->assertSessionMissing('remember');
    }

    public function test_it_sets_a_remember_cookie_for_a_user_with_a_password()
    {
        $user = User::factory()->create();
        $socialAccount = SocialAccount::factory()->make(['provider' => $this->provider]);
        $user->addSocialAccount($socialAccount);

        $this->mockSocialite($user->email, $user->name, $socialAccount->provider_user_id);

        $response = $this
            ->withSession(['remember' => true])
            ->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertCookie(Auth::guard()->getRecallerName());
        $this->assertAuthenticatedAs($user);
    }

    public function test_it_does_not_set_a_remember_cookie_for_a_user_without_a_password()
    {
        $user = User::factory()->create(['password' => null, 'remember_token' => null]);
        $socialAccount = SocialAccount::factory()->make(['provider' => $this->provider]);
        $user->addSocialAccount($socialAccount);

        $this->mockSocialite($user->email, $user->name, $socialAccount->provider_user_id);

        $response = $this
            ->withSession(['remember' => true])
            ->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertCookieMissing(Auth::guard()->getRecallerName());
        $this->assertAuthenticatedAs($user);
        $this->assertNull($user->fresh()->remember_token);
    }

    public function test_it_does_not_set_a_remember_cookie_for_a_new_user()
    {
        $this->enableUserCreation();

        $this->mockSocialite();

        $response = $this
            ->withSession(['remember' => true])
            ->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertCookieMissing(Auth::guard()->getRecallerName());
        $response->assertSessionMissing('remember');
        $this->assertTrue(Auth::check());
    }
}
