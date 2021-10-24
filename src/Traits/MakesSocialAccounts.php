<?php

namespace audunru\SocialAccounts\Traits;

use audunru\SocialAccounts\Models\SocialAccount;

trait MakesSocialAccounts
{
    /**
     * Make a new social account that can be added to a user.
     */
    private function makeSocialAccount(string $provider, string $provider_user_id): SocialAccount
    {
        return new SocialAccount(['provider' => $provider, 'provider_user_id' => $provider_user_id]);
    }
}
