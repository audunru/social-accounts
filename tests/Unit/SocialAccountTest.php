<?php

namespace audunru\SocialAccounts\Tests\Unit;

use audunru\SocialAccounts\Tests\TestCase;
use audunru\SocialAccounts\Tests\Models\User;
use audunru\SocialAccounts\Models\SocialAccount;

class SocialAccountTest extends TestCase
{
    private $user;
    private $socialAccount;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        $this->socialAccount = factory(SocialAccount::class)->make();
        $this->user->addSocialAccount($this->socialAccount);
    }

    public function test_it_shows_social_account()
    {
        $found = SocialAccount::find($this->socialAccount->id);

        $this->assertInstanceOf(SocialAccount::class, $found);
        $this->assertEquals($this->socialAccount->provider, $found->provider);
        $this->assertEquals($this->socialAccount->provider_user_id, $found->provider_user_id);
    }

    public function test_it_updates_social_account()
    {
        $updated = factory(SocialAccount::class)->make();

        $data = [
            'provider'         => $updated->provider,
            'provider_user_id' => $updated->provider_user_id,
        ];

        $update = $this->socialAccount->update($data);

        $this->assertTrue($update);
        $this->assertEquals($data['provider'], $this->socialAccount->provider);
        $this->assertEquals($data['provider_user_id'], $this->socialAccount->provider_user_id);
    }

    public function test_it_deletes_social_account()
    {
        $delete = $this->socialAccount->delete();

        $this->assertTrue($delete);
    }
}
