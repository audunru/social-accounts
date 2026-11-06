<?php

namespace audunru\SocialAccounts\Tests\Unit;

use Illuminate\Config\Repository;

class HasSocialAccountsCustomKeyTest extends HasSocialAccountsTest
{
    protected function defineEnvironment($app)
    {
        tap($app['config'], function (Repository $config) {
            $config->set('social-accounts.column_names.foreign_key', 'owner_id');
        });

        parent::defineEnvironment($app);
    }
}
