<?php

namespace audunru\SocialAccounts\Tests\Feature;

use Illuminate\Support\Facades\Event;
use audunru\SocialAccounts\Tests\TestCase;
use audunru\SocialAccounts\Tests\Models\User;
use audunru\SocialAccounts\Models\SocialAccount;
use audunru\SocialAccounts\Facades\SocialAccounts;
use audunru\SocialAccounts\Events\SocialAccountAdded;

class ApiTest extends TestCase
{
    protected $prefix = 'social-accounts';
    private $user;
    private $socialAccount;
    private $socialAccountStructure;

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

    public function test_an_event_is_dispatched_when_social_account_is_added()
    {
        Event::fake();

        $user = factory(User::class)->create();
        $socialAccount = factory(SocialAccount::class)->make();
        $user->addSocialAccount($socialAccount);

        Event::assertDispatched(SocialAccountAdded::class, function ($event) use ($user, $socialAccount) {
            return $event->user->is($user) && $event->socialAccount->is($socialAccount);
        });
    }
}
