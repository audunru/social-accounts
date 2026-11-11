<?php

namespace Tests\Feature;

use audunru\SocialAccounts\Exceptions\SocialAccountBelongsToAnotherUser;
use audunru\SocialAccounts\Models\SocialAccount;
use audunru\SocialAccounts\Tests\Models\User;
use audunru\SocialAccounts\Tests\TestCase;
use Illuminate\Contracts\Debug\ExceptionHandler;

/**
 * @SuppressWarnings("unused")
 */
class IntendedUrlTest extends TestCase
{
    protected $provider = 'google';

    protected $prefix = 'social-accounts';

    public function test_exception_handler_can_redirect_to_intended_url_when_social_account_belongs_to_another_user()
    {
        $this->enableSocialAccountCreation();

        $this->app->make(ExceptionHandler::class)
            ->renderable(fn (SocialAccountBelongsToAnotherUser $e) => redirect()->intended('/'));

        $user = User::factory()->create();
        $socialAccount = SocialAccount::factory()->make(['provider' => $this->provider]);
        $user->addSocialAccount($socialAccount);

        $this->mockSocialite($user->email, $user->name, $socialAccount->provider_user_id);

        $response = $this
            ->actingAs(User::factory()->create())
            ->withSession(['url.intended' => '/custom-url', 'social-accounts.redirect' => '/custom-url'])
            ->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertRedirect('/custom-url');
        $response->assertSessionMissing('url.intended');
    }

    public function test_intended_url_that_the_app_set_is_kept_when_the_callback_aborts()
    {
        $this->disableUserCreation();

        $this->mockSocialite();

        $response = $this
            ->withSession(['url.intended' => '/dashboard'])
            ->get("/{$this->prefix}/login/{$this->provider}/callback");

        $response->assertStatus(401);
        $response->assertSessionHas('url.intended', '/dashboard');
    }
}
