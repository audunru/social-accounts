<?php

namespace audunru\SocialAccounts\Tests\Feature;

use audunru\SocialAccounts\Models\SocialAccount;
use audunru\SocialAccounts\SocialAccounts;
use audunru\SocialAccounts\Tests\Models\User;
use audunru\SocialAccounts\Tests\TestCase;
use Mockery;

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
        $this->user = User::factory()->create();
        $this->socialAccount = SocialAccount::factory()->make();
        $this->user->addSocialAccount($this->socialAccount);
        $this->socialAccountStructure = [
            'id',
            'provider',
            'provider_user_id',
            'created_at',
            'updated_at',
        ];
    }

    public function testItFailsToGetSocialAccountsWhenUnauthenticated()
    {
        $response = $this->json('GET', "/{$this->prefix}");

        $response
            ->assertStatus(401);
    }

    public function testItGetsSocialAccounts()
    {
        $response = $this->actingAs($this->user, 'api')->json('GET', "/{$this->prefix}");

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [$this->socialAccountStructure]]);
    }

    public function testItGetsSocialAccountsButThereAreNone()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->json('GET', "/{$this->prefix}");

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data' => []]);
    }

    public function testItGetsSocialAccountsFromADifferentEndpoint()
    {
        config(['social-accounts.route_prefix' => 'awesome-path']);
        SocialAccounts::routes();

        $response = $this->actingAs($this->user, 'api')->json('GET', '/awesome-path');

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [$this->socialAccountStructure]]);
    }

    public function testItGetsASocialAccount()
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

    public function testItGetsASocialAccountFromADifferentEndpoint()
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

    public function testItDeletesASocialAccount()
    {
        $response = $this->actingAs($this->user, 'api')->json('DELETE', "/{$this->prefix}/{$this->socialAccount->id}");

        $response
            ->assertStatus(200)
            ->assertJson(['message' => 'Deleted']);
    }

    public function testItFailsToGetAnotherUsersSocialAccount()
    {
        $anotherUser = User::factory()->create();
        $anotherSocialAccount = SocialAccount::factory()->make();
        $anotherUser->addSocialAccount($anotherSocialAccount);

        $response = $this->actingAs($this->user, 'api')->json('GET', "/{$this->prefix}/{$anotherSocialAccount->id}");

        $response
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    public function testItFailsToDeleteAnotherUsersSocialAccount()
    {
        $anotherUser = User::factory()->create();
        $anotherSocialAccount = SocialAccount::factory()->make();
        $anotherUser->addSocialAccount($anotherSocialAccount);

        $response = $this->actingAs($this->user, 'api')->json('DELETE', "/{$this->prefix}/{$anotherSocialAccount->id}");

        $response
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    public function testItFailsToUpdateASocialAccount()
    {
        $response = $this->actingAs($this->user, 'api')->json('PATCH', "/{$this->prefix}/{$this->socialAccount->id}", [
            'provider' => 'vandelay-industries',
        ]);

        $response
            ->assertStatus(405);
    }

    public function testItReturnsAServerErrorWhenExistingAccountCantBeDeleted()
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
