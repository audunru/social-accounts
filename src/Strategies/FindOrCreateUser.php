<?php

namespace audunru\SocialAccounts\Strategies;

use Laravel\Socialite\Contracts\User;
use audunru\SocialAccounts\Interfaces\Strategy;
use audunru\SocialAccounts\Traits\FindsAndCreatesUsers;

class FindOrCreateUser implements Strategy
{
    use FindsAndCreatesUsers;

    public function handle(string $provider, User $providerUser)
    {
        return $this->findOrCreateUser($provider, $providerUser);
    }
}
