<?php

namespace audunru\SocialAccounts\Tests\Feature;

use Illuminate\Support\Facades\Schema;
use audunru\SocialAccounts\Tests\TestCase;

class MigrationTest extends TestCase
{
    public function test_social_accounts_table_has_correct_columns()
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
