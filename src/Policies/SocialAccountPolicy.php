<?php

namespace audunru\SocialAccounts\Policies;

use audunru\SocialAccounts\Models\SocialAccount;
use Illuminate\Auth\Access\HandlesAuthorization;
use Illuminate\Foundation\Auth\User as Authenticatable;

class SocialAccountPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view any social accounts.
     */
    public function viewAny(): bool
    {
        return true;
    }

    /**
     * Determine whether the user can view the social account.
     */
    public function view(Authenticatable $user, SocialAccount $socialAccount): bool
    {
        return $socialAccount->user->is($user);
    }

    /**
     * Determine whether the user can delete the social account.
     */
    public function delete(Authenticatable $user, SocialAccount $socialAccount): bool
    {
        return $socialAccount->user->is($user);
    }
}
