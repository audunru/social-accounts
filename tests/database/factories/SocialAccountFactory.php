<?php

use audunru\SocialAccounts\Models\SocialAccount;
use Faker\Generator as Faker;
use Illuminate\Support\Arr;

$factory->define(SocialAccount::class, function (Faker $faker) {
    return [
        'provider'         => Arr::random(['facebook', 'twitter', 'linkedin', 'google', 'github', 'gitlab', 'bitbucket']),
        'provider_user_id' => $faker->uuid,
    ];
});
