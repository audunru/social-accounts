<?php

namespace audunru\SocialAccounts\Events;

use audunru\SocialAccounts\Models\SocialAccount;
use Illuminate\Database\Eloquent\Model as User;
use Illuminate\Queue\SerializesModels;
use Laravel\Socialite\Contracts\User as ProviderUser;

class SocialAccountAdded
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(public User $user, public SocialAccount $socialAccount, public ProviderUser $providerUser)
    {
    }
}
