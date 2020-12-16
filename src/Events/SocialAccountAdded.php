<?php

namespace audunru\SocialAccounts\Events;

use audunru\SocialAccounts\Models\SocialAccount;
use Illuminate\Database\Eloquent\Model as User;
use Illuminate\Queue\SerializesModels;
use Laravel\Socialite\Contracts\User as ProviderUser;

class SocialAccountAdded
{
    use SerializesModels;

    /**
     * The user which the social account was added to.
     *
     * @var \Illuminate\Database\Eloquent\Model
     */
    public $user;

    /**
     * The social account which was added.
     *
     * @var SocialAccount
     */
    public $socialAccount;

    /**
     * The user returned by the Socialite provider.
     *
     * @var \Laravel\Socialite\Contracts\User
     */
    public $providerUser;

    /**
     * Create a new event instance.
     */
    public function __construct(User $user, SocialAccount $socialAccount, ProviderUser $providerUser)
    {
        $this->user = $user;
        $this->socialAccount = $socialAccount;
        $this->providerUser = $providerUser;
    }
}
