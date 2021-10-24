<?php

namespace audunru\SocialAccounts\Strategies;

use audunru\SocialAccounts\Interfaces\Strategy;
use audunru\SocialAccounts\Traits\FindsAndCreatesUsers;
use Illuminate\Database\Eloquent\Model as User;
use Laravel\Socialite\Contracts\User as ProviderUser;

class FindUser implements Strategy
{
    use FindsAndCreatesUsers;

    /**
     * Find a user with a social account.
     */
    public function handle(string $provider, ProviderUser $providerUser): ?User
    {
        return $this->findUser($provider, $providerUser);
    }
}
