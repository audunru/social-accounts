<?php

namespace audunru\SocialAccounts\Strategies;

use audunru\SocialAccounts\Interfaces\Strategy;
use Illuminate\Database\Eloquent\Model as User;
use Laravel\Socialite\Contracts\User as ProviderUser;
use audunru\SocialAccounts\Traits\FindsAndCreatesUsers;

class FindUser implements Strategy
{
    use FindsAndCreatesUsers;

    /**
     * Find a user with a social account.
     *
     * @param string                            $provider
     * @param \Laravel\Socialite\Contracts\User $providerUser
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function handle(string $provider, ProviderUser $providerUser): ?User
    {
        return $this->findUser($provider, $providerUser);
    }
}
