<?php

namespace audunru\SocialAccounts\Facades;

use Illuminate\Support\Facades\Facade;

class SocialAccounts extends Facade
{
    protected static function getFacadeAccessor()
    {
        return 'social-accounts';
    }
}
