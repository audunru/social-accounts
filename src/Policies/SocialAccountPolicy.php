<?php

namespace audunru\SocialAccounts\Policies;

use Illuminate\Database\Eloquent\Model as User;
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
}
