<?php

namespace audunru\SocialAccounts\Strategies;

use audunru\SocialAccounts\Events\SocialAccountAdded;
use audunru\SocialAccounts\Interfaces\Strategy;
use audunru\SocialAccounts\Traits\MakesSocialAccounts;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Contracts\User as ProviderUser;

class AddSocialAccount implements Strategy
{
    use MakesSocialAccounts;

    /**
     * Add social account to user.
     */
    public function handle(string $provider, ProviderUser $providerUser): Authenticatable
    {
        /** @var \Illuminate\Contracts\Auth\Authenticatable&\audunru\SocialAccounts\Traits\HasSocialAccounts */
        $user = Auth::user();
        $socialAccount = $this->makeSocialAccount($provider, $providerUser->getId());
        $user->addSocialAccount($socialAccount);

        event(new SocialAccountAdded($user, $socialAccount, $providerUser));

        return $user;
    }
}
