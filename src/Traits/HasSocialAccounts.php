<?php

namespace audunru\SocialAccounts\Traits;

use audunru\SocialAccounts\Models\SocialAccount;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @template TModel of \audunru\SocialAccounts\Models\SocialAccount
 *
 * @mixin \Illuminate\Database\Eloquent\Model
 */
trait HasSocialAccounts
{
    /**
     * Retrieve related social accounts.
     *
     * @return HasMany<TModel, $this>
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
     * @return TModel|false
     */
    public function addSocialAccount(SocialAccount $socialAccount): SocialAccount|false
    {
        return $this->socialAccounts()->save($socialAccount);
    }

    /**
     * Retrieve a user with social account matching the parameters.
     *
     * @return $this|null
     */
    public static function findBySocialAccount(string $provider, string $providerUserId): ?self
    {
        return self::whereHas('socialAccounts', function (Builder $query) use ($provider, $providerUserId) {
            $query->where('provider', '=', $provider)
                ->where('provider_user_id', '=', $providerUserId);
        })->first();
    }

    /**
     * Check if user already has social account with this provider.
     */
    public function hasProvider(string $provider): bool
    {
        return $this->whereHas('socialAccounts', function (Builder $query) use ($provider) {
            $query->where('provider', '=', $provider)
                ->where('user_id', '=', $this->id);
        })->get()->isNotEmpty();
    }
}
