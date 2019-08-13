<?php

namespace audunru\SocialAccounts\Strategies;

use Illuminate\Support\Facades\Auth;
use audunru\SocialAccounts\Interfaces\Strategy;
use Illuminate\Database\Eloquent\Model as User;
use audunru\SocialAccounts\Events\SocialAccountAdded;
use Laravel\Socialite\Contracts\User as ProviderUser;
use audunru\SocialAccounts\Traits\MakesSocialAccounts;

class AddSocialAccount implements Strategy
{
    use MakesSocialAccounts;

    /**
     * Add social account to user.
     *
     * @param string                            $provider
     * @param \Laravel\Socialite\Contracts\User $providerUser
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function handle(string $provider, ProviderUser $providerUser): User
    {
        $user = Auth::user();
        $socialAccount = $this->makeSocialAccount($provider, $providerUser->getId());
        $user->addSocialAccount($socialAccount);

        event(new SocialAccountAdded($user, $socialAccount, $providerUser));

        return $user;
    }
}
