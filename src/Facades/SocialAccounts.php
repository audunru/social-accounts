<?php

namespace audunru\SocialAccounts\Facades;

use Illuminate\Support\Facades\Facade;

class SocialAccounts extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor(): string
    {
        return 'social-accounts';
    }
}
