<?php

namespace audunru\SocialAccounts\Strategies;

use audunru\SocialAccounts\Interfaces\Strategy;
use audunru\SocialAccounts\Traits\FindsAndCreatesUsers;
use Laravel\Socialite\Contracts\User;

class FindOrCreateUser implements Strategy
{
    use FindsAndCreatesUsers;

    public function handle(string $provider, User $providerUser)
    {
        return $this->findOrCreateUser($provider, $providerUser->getEmail(), $providerUser->getName(), $providerUser->getId());
    }
}
