<?php

namespace audunru\SocialAccounts\Tests\Feature;

use audunru\SocialAccounts\Models\SocialAccount;
use audunru\SocialAccounts\Tests\Models\User;
use audunru\SocialAccounts\Tests\TestCase;
use Mockery;
use SocialAccounts;

class ApiTest extends TestCase
{
    protected $prefix = 'social-accounts';
    protected $user;
    protected $socialAccount;
    protected $socialAccountStructure;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = factory(User::class)->create();
        $this->socialAccount = factory(SocialAccount::class)->make();
        $this->user->addSocialAccount($this->socialAccount);
        $this->socialAccountStructure = [
            'id',
            'provider',
            'provider_user_id',
            'created_at',
            'updated_at',
        ];
    }

    public function test_it_fails_to_get_social_accounts_when_unauthenticated()
    {
        $response = $this->json('GET', "/{$this->prefix}");

        $response
            ->assertStatus(401);
    }

    public function test_it_gets_social_accounts()
    {
        $response = $this->actingAs($this->user, 'api')->json('GET', "/{$this->prefix}");

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [$this->socialAccountStructure]]);
    }

    public function test_it_gets_social_accounts_but_there_are_none()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user, 'api')->json('GET', "/{$this->prefix}");

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data' => []]);
    }

    public function test_it_gets_social_accounts_from_a_different_endpoint()
    {
        config(['social-accounts.route_prefix' => 'awesome-path']);
        SocialAccounts::routes();

        $response = $this->actingAs($this->user, 'api')->json('GET', '/awesome-path');

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [$this->socialAccountStructure]]);
    }

    public function test_it_gets_a_social_account()
    {
        $response = $this->actingAs($this->user, 'api')->json('GET', "/{$this->prefix}/{$this->socialAccount->id}");

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data' => $this->socialAccountStructure])
            ->assertJson(['data' => [
                'id'               => $this->socialAccount->id,
                'provider'         => $this->socialAccount->provider,
                'provider_user_id' => $this->socialAccount->provider_user_id,
            ]]);
    }

    public function test_it_gets_a_social_account_from_a_different_endpoint()
    {
        config(['social-accounts.route_prefix' => 'awesome-path']);
        SocialAccounts::routes();

        $response = $this->actingAs($this->user, 'api')->json('GET', "/awesome-path/{$this->socialAccount->id}");

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data' => $this->socialAccountStructure])
            ->assertJson(['data' => [
                'id'               => $this->socialAccount->id,
                'provider'         => $this->socialAccount->provider,
                'provider_user_id' => $this->socialAccount->provider_user_id,
            ]]);
    }

    public function test_it_deletes_a_social_account()
    {
        $response = $this->actingAs($this->user, 'api')->json('DELETE', "/{$this->prefix}/{$this->socialAccount->id}");

        $response
            ->assertStatus(200)
            ->assertJson(['message' => 'Deleted']);
    }

    public function test_it_fails_to_get_another_users_social_account()
    {
        $anotherUser = factory(User::class)->create();
        $anotherSocialAccount = factory(SocialAccount::class)->make();
        $anotherUser->addSocialAccount($anotherSocialAccount);

        $response = $this->actingAs($this->user, 'api')->json('GET', "/{$this->prefix}/{$anotherSocialAccount->id}");

        $response
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    public function test_it_fails_to_delete_another_users_social_account()
    {
        $anotherUser = factory(User::class)->create();
        $anotherSocialAccount = factory(SocialAccount::class)->make();
        $anotherUser->addSocialAccount($anotherSocialAccount);

        $response = $this->actingAs($this->user, 'api')->json('DELETE', "/{$this->prefix}/{$anotherSocialAccount->id}");

        $response
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    public function test_it_fails_to_update_a_social_account()
    {
        $response = $this->actingAs($this->user, 'api')->json('PATCH', "/{$this->prefix}/{$this->socialAccount->id}", [
            'provider' => 'vandelay-industries',
        ]);

        $response
            ->assertStatus(405);
    }

    public function test_it_returns_a_server_error_when_existing_account_cant_be_deleted()
    {
        $socialAccount = Mockery::mock('audunru\SocialAccounts\Models\SocialAccount');

        $socialAccount
            ->shouldReceive('delete')
            ->andReturn(false);

        $this->app->instance('audunru\SocialAccounts\Models\SocialAccount', $socialAccount);

        $response = $this->actingAs($this->user, 'api')->json('DELETE', "/{$this->prefix}/{$this->socialAccount->id}");

        $response
            ->assertStatus(500);
    }
}
