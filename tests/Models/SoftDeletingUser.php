<?php

namespace audunru\SocialAccounts\Tests\Models;

use Illuminate\Database\Eloquent\SoftDeletes;

class SoftDeletingUser extends User
{
    use SoftDeletes;
}
