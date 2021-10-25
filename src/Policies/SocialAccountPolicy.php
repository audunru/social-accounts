<?php

namespace audunru\SocialAccounts\Policies;

use audunru\SocialAccounts\Models\SocialAccount;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Database\Eloquent\Model as User;

class SocialAccountPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any social accounts.
     *
     * @return mixed
     */
    public function viewAny(): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the social account.
     *
     * @return mixed
     */
    public function view(User $user, SocialAccount $socialAccount): bool
    {
        return $socialAccount->user->is($user);  // TODO: Perhaps this can be defined only once, as it's the same rule for view and delete.
    }

    /**
     * Determine whether the user can delete the social account.
     *
     * @return mixed
     */
    public function delete(User $user, SocialAccount $socialAccount): bool
    {
        return $socialAccount->user->is($user);
    }
}
