<?php

namespace audunru\SocialAccounts\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class UserCreationDenied extends HttpException
{
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(403, 'You are not allowed to sign up with this provider.', $previous);
    }
}
