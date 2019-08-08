<?php

namespace audunru\SocialAccounts\Strategies;

use audunru\SocialAccounts\Interfaces\Strategy;
use audunru\SocialAccounts\Traits\FindsAndCreatesUsers;
use Laravel\Socialite\Contracts\User;

class FindUser implements Strategy
{
    use FindsAndCreatesUsers;

    public function handle(string $provider, User $providerUser)
    {
        return $this->findUser($provider, $providerUser->getId());
    }
}
