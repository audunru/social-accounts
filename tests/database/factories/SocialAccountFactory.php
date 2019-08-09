<?php

use Illuminate\Support\Arr;
use Faker\Generator as Faker;
use audunru\SocialAccounts\Models\SocialAccount;

$factory->define(SocialAccount::class, function (Faker $faker) {
    return [
        'provider'         => Arr::random(['facebook', 'twitter', 'linkedin', 'google', 'github', 'gitlab', 'bitbucket']),
        'provider_user_id' => $faker->uuid,
    ];
});
