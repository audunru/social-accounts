<?php

namespace audunru\SocialAccounts\Tests\Feature;

use audunru\SocialAccounts\Tests\TestCase;
use Illuminate\Support\Facades\Schema;

class MigrationTest extends TestCase
{
    public function testSocialAccountsTableHasCorrectColumns()
    {
        $columns = Schema::getColumnListing('social_accounts');
        $this->assertEquals([
            'id',
            'provider',
            'provider_user_id',
            'user_id',
            'created_at',
            'updated_at',
        ], $columns);
    }
}
