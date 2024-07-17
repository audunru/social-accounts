<?php

namespace audunru\SocialAccounts\Interfaces;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Socialite\Contracts\User as ProviderUser;

interface Strategy
{
    /**
     * Handle authentication of the provided user after succesful authorization with the provider.
     */
    public function handle(string $provider, ProviderUser $providerUser): ?Authenticatable;
}
