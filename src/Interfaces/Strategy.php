<?php

namespace audunru\SocialAccounts\Interfaces;

use Illuminate\Database\Eloquent\Model as User;
use Laravel\Socialite\Contracts\User as ProviderUser;

interface Strategy
{
    /**
     * Handle authentication of the provided user after succesful authorization with the provider.
     *
     * @return User|null
     */
    public function handle(string $provider, ProviderUser $providerUser): ?User;
}
