<?php

namespace audunru\SocialAccounts\Tests\Feature;

use audunru\SocialAccounts\Models\SocialAccount;
use audunru\SocialAccounts\Tests\Models\User;
use audunru\SocialAccounts\Tests\TestCase;
use Mockery;

class ApiTest extends TestCase
{
    protected $apiPath = 'social-accounts';
    protected $user;
    protected $socialAccount;
    protected $structure;

    /**
     * Setup the test environment.
     */
    protected function setUp(): void
    {
        parent::setUp();
        $this->user = User::factory()->create();
        $this->socialAccount = SocialAccount::factory()->make();
        $this->user->addSocialAccount($this->socialAccount);
        $this->structure = [
            'id',
            'provider',
            'provider_user_id',
            'created_at',
            'updated_at',
        ];
    }

    public function testItFailsToGetSocialAccountsWhenUnauthenticated()
    {
        $response = $this->json('GET', "/{$this->apiPath}");

        $response
            ->assertStatus(401);
    }

    public function testItGetsSocialAccounts()
    {
        $response = $this->actingAs($this->user, 'api')->json('GET', "/{$this->apiPath}");

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [$this->structure]]);
    }

    public function testItGetsSocialAccountsButThereAreNone()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user, 'api')->json('GET', "/{$this->apiPath}");

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data' => []]);
    }

    public function testItGetsASocialAccount()
    {
        $response = $this->actingAs($this->user, 'api')->json('GET', "/{$this->apiPath}/{$this->socialAccount->id}");

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data' => $this->structure])
            ->assertJson(['data' => [
                'id'               => $this->socialAccount->id,
                'provider'         => $this->socialAccount->provider,
                'provider_user_id' => $this->socialAccount->provider_user_id,
            ]]);
    }

    public function testItDeletesASocialAccount()
    {
        $response = $this->actingAs($this->user, 'api')->json('DELETE', "/{$this->apiPath}/{$this->socialAccount->id}");

        $response
            ->assertStatus(200)
            ->assertJson(['message' => 'Deleted']);
    }

    public function testItFailsToGetAnotherUsersSocialAccount()
    {
        $anotherUser = User::factory()->create();
        $anotherSocialAccount = SocialAccount::factory()->make();
        $anotherUser->addSocialAccount($anotherSocialAccount);

        $response = $this->actingAs($this->user, 'api')->json('GET', "/{$this->apiPath}/{$anotherSocialAccount->id}");

        $response
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    public function testItFailsToDeleteAnotherUsersSocialAccount()
    {
        $anotherUser = User::factory()->create();
        $anotherSocialAccount = SocialAccount::factory()->make();
        $anotherUser->addSocialAccount($anotherSocialAccount);

        $response = $this->actingAs($this->user, 'api')->json('DELETE', "/{$this->apiPath}/{$anotherSocialAccount->id}");

        $response
            ->assertStatus(403)
            ->assertJson(['message' => 'This action is unauthorized.']);
    }

    public function testItFailsToUpdateASocialAccount()
    {
        $response = $this->actingAs($this->user, 'api')->json('PATCH', "/{$this->apiPath}/{$this->socialAccount->id}", [
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

        $response = $this->actingAs($this->user, 'api')->json('DELETE', "/{$this->apiPath}/{$this->socialAccount->id}");

        $response
            ->assertStatus(500);
    }
}
