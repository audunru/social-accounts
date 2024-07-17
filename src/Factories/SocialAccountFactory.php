<?php

namespace audunru\SocialAccounts\Factories;

use audunru\SocialAccounts\Models\SocialAccount;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Arr;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\audunru\SocialAccounts\Models\SocialAccount>
 */
class SocialAccountFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     */
    protected $model = SocialAccount::class;

    /**
     * Define the model's default state.
     */
    public function definition()
    {
        return [
            'provider'         => Arr::random(['facebook', 'twitter', 'linkedin', 'google', 'github', 'gitlab', 'bitbucket']),
            'provider_user_id' => $this->faker->uuid,
        ];
    }
}
