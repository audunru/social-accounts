<?php

namespace audunru\SocialAccounts\Tests\Unit;

use audunru\SocialAccounts\Models\SocialAccount;
use audunru\SocialAccounts\Tests\Models\User;
use audunru\SocialAccounts\Tests\TestCase;
use Illuminate\Database\Eloquent\Collection;

class HasSocialAccountsTest extends TestCase
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

    public function test_it_adds_social_account()
    {
        $user = factory(User::class)->create();
        $socialAccount = factory(SocialAccount::class)->make();

        $data = [
            'provider'         => $socialAccount->provider,
            'provider_user_id' => $socialAccount->provider_user_id,
        ];

        $created = $user->addSocialAccount(new SocialAccount($data));

        $this->assertInstanceOf(SocialAccount::class, $created);
        $this->assertEquals($data['provider'], $created->provider);
        $this->assertEquals($data['provider_user_id'], $created->provider_user_id);
    }

    public function test_user_has_social_accounts()
    {
        $this->assertInstanceOf(Collection::class, $this->user->socialAccounts);
        $this->assertEquals(1, $this->user->socialAccounts->count());
        $this->assertEquals($this->socialAccount->id, $this->user->socialAccounts()->first()->id);
        $this->assertEquals($this->socialAccount->provider, $this->user->socialAccounts()->first()->provider);
        $this->assertEquals($this->socialAccount->provider_user_id, $this->user->socialAccounts()->first()->provider_user_id);
    }

    public function test_it_finds_a_user_by_social_account()
    {
        $user = User::findBySocialAccount($this->socialAccount->provider, $this->socialAccount->provider_user_id);

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($this->user->id, $user->id);
    }

    public function test_user_has_provider()
    {
        $this->assertTrue($this->user->hasProvider($this->socialAccount->provider));
    }

    public function test_user_does_not_have_provider()
    {
        $this->assertFalse($this->user->hasProvider('a-different-provider'));
    }
}
