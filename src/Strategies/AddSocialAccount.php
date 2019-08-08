<?php

namespace audunru\SocialAccounts\Strategies;

use audunru\SocialAccounts\Interfaces\Strategy;
use audunru\SocialAccounts\Traits\MakesSocialAccounts;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Contracts\User;

class AddSocialAccount implements Strategy
{
    use MakesSocialAccounts;

    public function handle(string $provider, User $providerUser)
    {
        $socialAccount = $this->makeSocialAccount($provider, $providerUser->getId());
        Auth::user()->addSocialAccount($socialAccount);

        return Auth::user();
    }
}
