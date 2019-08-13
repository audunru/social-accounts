<?php

namespace audunru\SocialAccounts\Strategies;

use audunru\SocialAccounts\Interfaces\Strategy;
use Illuminate\Database\Eloquent\Model as User;
use Laravel\Socialite\Contracts\User as ProviderUser;
use audunru\SocialAccounts\Traits\FindsAndCreatesUsers;

class FindOrCreateUser implements Strategy
{
    use FindsAndCreatesUsers;

    /**
     * Find a user, or create a new one.
     *
     * @param string                            $provider
     * @param \Laravel\Socialite\Contracts\User $providerUser
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function handle(string $provider, ProviderUser $providerUser): User
    {
        return $this->findOrCreateUser($provider, $providerUser);
    }
}
