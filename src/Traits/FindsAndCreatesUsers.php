<?php

namespace audunru\SocialAccounts\Traits;

use audunru\SocialAccounts\Events\SocialUserCreated;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Socialite\Contracts\User as ProviderUser;

trait FindsAndCreatesUsers
{
    use MakesSocialAccounts;

    /**
     * Find a user with a social account.
     */
    private function findUser(string $provider, ProviderUser $providerUser): ?Authenticatable
    {
        /** @var Authenticatable $userModel */
        $userModel = config('social-accounts.models.user');

        return $userModel::findBySocialAccount($provider, $providerUser->getId());
    }

    /**
     * Create a new user with a social account.
     */
    private function createUser(string $provider, ProviderUser $providerUser): Authenticatable
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
     */
    private function findOrCreateUser(string $provider, ProviderUser $providerUser): Authenticatable
    {
        $user = $this->findUser($provider, $providerUser);
        if (! is_null($user)) {
            return $user;
        }

        return $this->createUser($provider, $providerUser);
    }
}
