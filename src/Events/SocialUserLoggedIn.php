<?php

namespace audunru\SocialAccounts\Events;

use Illuminate\Database\Eloquent\Model as User;
use Illuminate\Queue\SerializesModels;
use Laravel\Socialite\Contracts\User as ProviderUser;

class SocialUserLoggedIn
{
    use SerializesModels;

    /**
     * Create a new event instance.
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function __construct(public User $user, public string $provider, public ProviderUser $providerUser) {}
}
