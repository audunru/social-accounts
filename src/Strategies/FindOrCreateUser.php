<?php

namespace audunru\SocialAccounts\Strategies;

use audunru\SocialAccounts\Interfaces\Strategy;
use audunru\SocialAccounts\Traits\FindsAndCreatesUsers;
use Illuminate\Database\Eloquent\Model as User;
use Laravel\Socialite\Contracts\User as ProviderUser;

class FindOrCreateUser implements Strategy
{
    use FindsAndCreatesUsers;

    /**
     * Find a user, or create a new one.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function handle(string $provider, ProviderUser $providerUser): User
    {
        return $this->findOrCreateUser($provider, $providerUser);
    }
}
