<?php

namespace audunru\SocialAccounts\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;

class SocialAccountBelongsToAnotherUser extends HttpException
{
    public function __construct()
    {
        parent::__construct(409, 'This social login belongs to another user.');
    }
}
