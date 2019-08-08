<?php

namespace audunru\SocialAccounts\Tests\Models;

use audunru\SocialAccounts\Traits\HasSocialAccounts;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasSocialAccounts;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'email', 'password',
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);
        $this->setTable(config('social-accounts.table_names.users'));
    }
}
