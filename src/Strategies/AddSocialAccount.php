<?php

namespace audunru\SocialAccounts\Strategies;

use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Contracts\User;
use audunru\SocialAccounts\Interfaces\Strategy;
use audunru\SocialAccounts\Events\SocialAccountAdded;
use audunru\SocialAccounts\Traits\MakesSocialAccounts;

class AddSocialAccount implements Strategy
{
    use MakesSocialAccounts;

    public function handle(string $provider, User $providerUser)
    {
        $user = Auth::user();
        $socialAccount = $this->makeSocialAccount($provider, $providerUser->getId());
        $user->addSocialAccount($socialAccount);

        event(new SocialAccountAdded($user, $socialAccount, $providerUser));

        return $user;
    }
}
