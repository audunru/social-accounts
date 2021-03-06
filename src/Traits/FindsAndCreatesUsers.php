<?php

namespace audunru\SocialAccounts\Traits;

use audunru\SocialAccounts\Events\SocialUserCreated;
use Illuminate\Database\Eloquent\Model as User;
use Laravel\Socialite\Contracts\User as ProviderUser;

trait FindsAndCreatesUsers
{
    use MakesSocialAccounts;

    /**
     * Find a user with a social account.
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    private function findUser(string $provider, ProviderUser $providerUser): ?User
    {
        return config('social-accounts.models.user')::findBySocialAccount($provider, $providerUser->getId());
    }

    /**
     * Create a new user with a social account.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    private function createUser(string $provider, ProviderUser $providerUser): User
    {
        $user = config('social-accounts.models.user')::create([
            'email'    => $providerUser->getEmail(),
            'name'     => $providerUser->getName(),
        ]);
        $socialAccount = $this->makeSocialAccount($provider, $providerUser->getId());
        $user->addSocialAccount($socialAccount);

        event(new SocialUserCreated($user, $socialAccount, $providerUser));

        return $user;
    }

    /**
     * Find a user, or create a new one.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    private function findOrCreateUser(string $provider, ProviderUser $providerUser): User
    {
        if ($user = $this->findUser($provider, $providerUser)) {
            return $user;
        }

        return $this->createUser($provider, $providerUser);
    }
}
