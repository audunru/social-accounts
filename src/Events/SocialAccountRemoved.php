<?php

namespace audunru\SocialAccounts\Events;

use audunru\SocialAccounts\Models\SocialAccount;
use Illuminate\Database\Eloquent\Model as User;

/**
 * The social account is already deleted when this event is dispatched. The event does not use
 * SerializesModels, because a queued listener could not load the deleted account again.
 */
class SocialAccountRemoved
{
    /**
     * Create a new event instance.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(public User $user, public SocialAccount $socialAccount) {}
}
