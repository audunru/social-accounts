<?php

namespace audunru\SocialAccounts\Events;

use Illuminate\Database\Eloquent\Model as User;
use Illuminate\Queue\SerializesModels;

/**
 * The social account is already deleted when this event is dispatched, so the event holds its
 * provider and provider user ID instead of the model. A queued listener could not load the model again.
 */
class SocialAccountRemoved
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(public User $user, public string $provider, public string $providerUserId) {}
}
