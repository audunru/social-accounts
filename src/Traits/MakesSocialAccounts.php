<?php

namespace audunru\SocialAccounts\Traits;

use audunru\SocialAccounts\Models\SocialAccount;

/**
 * @template TModel of \audunru\SocialAccounts\Models\SocialAccount
 */
trait MakesSocialAccounts
{
    /**
     * Make a new social account that can be added to a user.
     *
     * @return TModel
     */
    private function makeSocialAccount(string $provider, string $providerUserId): SocialAccount
    {
        return new SocialAccount(['provider' => $provider, 'provider_user_id' => $providerUserId]);
    }
}
