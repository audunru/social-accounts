<?php

namespace Tests\Feature;

use audunru\SocialAccounts\Events\SocialAccountRemoved;
use audunru\SocialAccounts\Models\SocialAccount;
use audunru\SocialAccounts\Tests\Models\User;
use audunru\SocialAccounts\Tests\TestCase;
use Illuminate\Support\Facades\Event;

class SocialAccountRemovedTest extends TestCase
{
    protected $apiPath = 'social-accounts';

    public function test_removed_event_is_dispatched_when_social_account_is_deleted()
    {
        Event::fake([SocialAccountRemoved::class]);

        $user = User::factory()->create();
        $socialAccount = SocialAccount::factory()->make();
        $user->addSocialAccount($socialAccount);

        $response = $this->actingAs($user, 'api')->json('DELETE', "/{$this->apiPath}/{$socialAccount->id}");

        $response->assertStatus(200);
        Event::assertDispatched(SocialAccountRemoved::class, function ($event) use ($user, $socialAccount) {
            return $event->user->is($user)
                && $event->provider === $socialAccount->provider
                && $event->providerUserId === $socialAccount->provider_user_id;
        });
    }

    public function test_removed_event_can_be_serialized_for_a_queued_listener()
    {
        Event::fake([SocialAccountRemoved::class]);

        $user = User::factory()->create();
        $socialAccount = SocialAccount::factory()->make();
        $user->addSocialAccount($socialAccount);

        $this->actingAs($user, 'api')->json('DELETE', "/{$this->apiPath}/{$socialAccount->id}");

        Event::assertDispatched(SocialAccountRemoved::class, function ($event) use ($user, $socialAccount) {
            $serialized = serialize($event);
            $restored = unserialize($serialized);

            return ! str_contains($serialized, $user->password)
                && $restored->user->is($user)
                && $restored->provider === $socialAccount->provider
                && $restored->providerUserId === $socialAccount->provider_user_id;
        });
    }

    public function test_removed_event_is_not_dispatched_when_policy_denies()
    {
        Event::fake([SocialAccountRemoved::class]);

        $user = User::factory()->create();
        $anotherUser = User::factory()->create();
        $socialAccount = SocialAccount::factory()->make();
        $anotherUser->addSocialAccount($socialAccount);

        $response = $this->actingAs($user, 'api')->json('DELETE', "/{$this->apiPath}/{$socialAccount->id}");

        $response->assertStatus(403);
        Event::assertNotDispatched(SocialAccountRemoved::class);
    }

    public function test_removed_event_is_not_dispatched_when_delete_fails()
    {
        Event::fake([SocialAccountRemoved::class]);

        $user = User::factory()->create();
        $socialAccount = SocialAccount::factory()->make();
        $user->addSocialAccount($socialAccount);

        SocialAccount::deleting(fn () => false);

        $response = $this->actingAs($user, 'api')->json('DELETE', "/{$this->apiPath}/{$socialAccount->id}");

        $response->assertStatus(500);
        Event::assertNotDispatched(SocialAccountRemoved::class);
    }
}
