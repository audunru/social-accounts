<?php

namespace audunru\SocialAccounts\Traits;

use audunru\SocialAccounts\Events\SocialUserCreated;
use audunru\SocialAccounts\Exceptions\EmailAlreadyRegistered;
use Illuminate\Auth\Events\Registered;
use Illuminate\Database\Eloquent\Model as User;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Str;
use Laravel\Socialite\Contracts\User as ProviderUser;

trait FindsAndCreatesUsers
{
    use MakesSocialAccounts;

    /**
     * Find a user with a social account.
     */
    private function findUser(string $provider, ProviderUser $providerUser): ?User
    {
        return config('social-accounts.models.user')::findBySocialAccount($provider, $providerUser->getId());
    }

    /**
     * Create a new user with a social account.
     */
    private function createUser(string $provider, ProviderUser $providerUser): User
    {
        $model = config('social-accounts.models.user');
        $email = $providerUser->getEmail();

        // No global scopes: the unique index also has soft-deleted users.
        // SQLite lower() changes only ASCII letters.
        throw_if(! is_null($email) && $model::withoutGlobalScopes()->whereRaw('lower(email) = ?', [Str::lower($email)])->exists(), EmailAlreadyRegistered::class);

        try {
            $user = $model::create([
                'email' => $email,
                'name' => $providerUser->getName(),
            ]);
        } catch (UniqueConstraintViolationException $e) {
            // Another request created the same email after the check (same method as createOrFirst()).
            throw_if(! is_null($email), new EmailAlreadyRegistered($e));

            throw $e;
        }

        $socialAccount = $this->makeSocialAccount($provider, $providerUser->getId());
        $user->addSocialAccount($socialAccount);

        event(new SocialUserCreated($user, $socialAccount, $providerUser));
        event(new Registered($user));

        return $user;
    }

    /**
     * Find a user, or create a new one.
     */
    private function findOrCreateUser(string $provider, ProviderUser $providerUser): User
    {
        $user = $this->findUser($provider, $providerUser);
        if (! is_null($user)) {
            return $user;
        }

        return $this->createUser($provider, $providerUser);
    }
}
