<?php

namespace audunru\SocialAccounts\Traits;

use audunru\SocialAccounts\Models\SocialAccount;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

trait HasSocialAccounts
{
    /**
     * Retrieve related social accounts.
     *
     * @return HasMany
     */
    public function socialAccounts(): HasMany
    {
        return $this->hasMany(
            config('social-accounts.models.social_account'),
            config('social-accounts.column_names.foreign_key'),
            config('social-accounts.column_names.primary_key')
        );
    }

    /**
     * Add social account to model.
     *
     * @param SocialAccount $socialAccount
     *
     * @return SocialAccount
     */
    public function addSocialAccount(SocialAccount $socialAccount): SocialAccount
    {
        return $this->socialAccounts()->save($socialAccount);
    }

    /**
     * Retrieve a user with social account matching the parameters.
     *
     * @param string $provider
     * @param string $provider_user_id
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public static function findBySocialAccount(string $provider, string $provider_user_id): ?self
    {
        return self::whereHas('socialAccounts', function (Builder $query) use ($provider, $provider_user_id) {
            $query->where('provider', '=', $provider)
                ->where('provider_user_id', '=', $provider_user_id);
        })->first();
    }

    /**
     * Check if user already has social account with this provider.
     *
     * @param string $provider
     *
     * @return bool
     */
    public function hasProvider(string $provider): bool
    {
        return $this->whereHas('socialAccounts', function (Builder $query) use ($provider) {
            $query->where('provider', '=', $provider)
                ->where('user_id', '=', $this->id);
        })->get()->isNotEmpty();
    }
}
