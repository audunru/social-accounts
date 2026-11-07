<?php

namespace audunru\SocialAccounts\Exceptions;

use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class EmailAlreadyRegistered extends HttpException
{
    public function __construct(?Throwable $previous = null)
    {
        parent::__construct(409, 'A user with this email address already exists.', $previous);
    }
}
