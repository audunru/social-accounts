<?php

namespace audunru\SocialAccounts\Strategies;

use Laravel\Socialite\Contracts\User;
use audunru\SocialAccounts\Interfaces\Strategy;
use audunru\SocialAccounts\Traits\FindsAndCreatesUsers;

class FindUser implements Strategy
{
    use FindsAndCreatesUsers;

    public function handle(string $provider, User $providerUser)
    {
        return $this->findUser($provider, $providerUser);
    }
}
