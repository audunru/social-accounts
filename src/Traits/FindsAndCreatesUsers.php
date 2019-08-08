<?php

namespace audunru\SocialAccounts\Traits;

use Illuminate\Foundation\Auth\User;

trait FindsAndCreatesUsers
{
    use MakesSocialAccounts;

    /**
     * Find a user with a social account.
     *
     * @param string $provider
     * @param string $provider_user_id
     *
     * @return User|null
     */
    private function findUser(string $provider, string $provider_user_id): ?User
    {
        return config('social-accounts.models.user')::findBySocialAccount($provider, $provider_user_id);
    }

    /**
     * Create a new user with a social account.
     *
     * @param string $provider
     * @param string $email
     * @param string $name
     * @param string $provider_user_id
     *
     * @return User
     */
    private function createUser(string $provider, string $email, string $name, string $provider_user_id): User
    {
        $user = config('social-accounts.models.user')::create([
            'email'    => $email,
            'name'     => $name,
        ]);

        $socialAccount = $this->makeSocialAccount($provider, $provider_user_id);

        $user->addSocialAccount($socialAccount);

        return $user;
    }

    /**
     * Find a user, or create a new one.
     *
     * @param string $provider
     * @param string $email
     * @param string $name
     * @param string $provider_user_id
     *
     * @return User
     */
    private function findOrCreateUser(string $provider, string $email, string $name, string $provider_user_id): User
    {
        if ($user = $this->findUser($provider, $provider_user_id)) {
            return $user;
        }

        return $this->createUser($provider, $email, $name, $provider_user_id);
    }
}
