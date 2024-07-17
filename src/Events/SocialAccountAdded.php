<?php

namespace audunru\SocialAccounts\Events;

use audunru\SocialAccounts\Models\SocialAccount;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Queue\SerializesModels;
use Laravel\Socialite\Contracts\User as ProviderUser;

class SocialAccountAdded
{
    use SerializesModels;

    public function __construct(public Authenticatable $user, public SocialAccount $socialAccount, public ProviderUser $providerUser)
    {
    }
}
