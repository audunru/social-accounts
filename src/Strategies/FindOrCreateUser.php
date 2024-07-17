<?php

namespace audunru\SocialAccounts\Strategies;

use audunru\SocialAccounts\Interfaces\Strategy;
use audunru\SocialAccounts\Traits\FindsAndCreatesUsers;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Socialite\Contracts\User as ProviderUser;

class FindOrCreateUser implements Strategy
{
    use FindsAndCreatesUsers;

    /**
     * Find a user, or create a new one.
     */
    public function handle(string $provider, ProviderUser $providerUser): Authenticatable
    {
        return $this->findOrCreateUser($provider, $providerUser);
    }
}
