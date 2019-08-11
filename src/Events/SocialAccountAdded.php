<?php

namespace audunru\SocialAccounts\Events;

use Illuminate\Queue\SerializesModels;
use Illuminate\Database\Eloquent\Model as User;
use audunru\SocialAccounts\Models\SocialAccount;
use Laravel\Socialite\Contracts\User as ProviderUser;

class SocialAccountAdded
{
    use SerializesModels;

    /**
     * The user which the social account was added to.
     *
     * @var User
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
     * @var ProviderUser
     */
    public $providerUser;

    /**
     * Create a new event instance.
     *
     * @param User          $user
     * @param SocialAccount $socialAccount
     * @param ProviderUser  $providerUser
     */
    public function __construct(User $user, SocialAccount $socialAccount, ProviderUser $providerUser)
    {
        $this->user = $user;
        $this->socialAccount = $socialAccount;
        $this->providerUser = $providerUser;
    }
}
