<?php

namespace audunru\SocialAccounts\Interfaces;

use Laravel\Socialite\Contracts\User;

interface Strategy
{
    public function handle(string $provider, User $providerUser);
}
