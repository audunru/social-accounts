<?php

namespace audunru\SocialAccounts\Events;

use Illuminate\Foundation\Auth\User;
use Illuminate\Queue\SerializesModels;
use audunru\SocialAccounts\Models\SocialAccount;

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
     * Create a new event instance.
     *
     * @param User          $user
     * @param SocialAccount $socialAccount
     */
    public function __construct(User $user, SocialAccount $socialAccount)
    {
        $this->user = $user;
        $this->socialAccount = $socialAccount;
    }
}
