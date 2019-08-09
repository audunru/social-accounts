<?php

namespace audunru\SocialAccounts\Policies;

use Illuminate\Foundation\Auth\User;
use audunru\SocialAccounts\Models\SocialAccount;
use Illuminate\Auth\Access\HandlesAuthorization;

class SocialAccountPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the social account.
     *
     * @param User          $user
     * @param SocialAccount $socialAccount
     *
     * @return mixed
     */
    public function view(User $user, SocialAccount $socialAccount): bool
    {
        return $socialAccount->user->is($user);
    }

    /**
     * Determine whether the user can create social accounts.
     *
     * @param User $user
     *
     * @return mixed
     */
    public function create(User $user): bool
    {
        return true; // All authenticated users can create social accounts
    }

    /**
     * Determine whether the user can update the social account.
     *
     * @param User          $user
     * @param SocialAccount $socialAccount
     *
     * @return mixed
     */
    public function update(User $user, SocialAccount $socialAccount): bool
    {
        return false; // Updating is not supported, so we return false just to be sure
    }

    /**
     * Determine whether the user can delete the social account.
     *
     * @param User          $user
     * @param SocialAccount $socialAccount
     *
     * @return mixed
     */
    public function delete(User $user, SocialAccount $socialAccount): bool
    {
        return $socialAccount->user->is($user);
    }

    /**
     * Determine whether the user can restore the social account.
     *
     * @param User          $user
     * @param SocialAccount $socialAccount
     *
     * @return mixed
     */
    public function restore(User $user, SocialAccount $socialAccount): bool
    {
        return false; // Soft deleting is not supported, so we return false just to be sure
    }

    /**
     * Determine whether the user can permanently delete the social account.
     *
     * @param User          $user
     * @param SocialAccount $socialAccount
     *
     * @return mixed
     */
    public function forceDelete(User $user, SocialAccount $socialAccount): bool
    {
        return false; // Soft deleting is not supported, so we return false just to be sure
    }
}
