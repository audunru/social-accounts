<?php

namespace audunru\SocialAccounts\Tests\Feature;

use Illuminate\Config\Repository;

class ApiTestWithCustomPath extends ApiTest
{
    protected function defineEnvironment($app)
    {
        tap($app['config'], function (Repository $config) {
            $config->set('social-accounts.api_path', 'awesome-path');
        });

        parent::defineEnvironment($app);
    }

    public function testItGetsSocialAccountsFromADifferentEndpoint()
    {
        $response = $this->actingAs($this->user, 'api')->json('GET', '/awesome-path');

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data' => [$this->structure]]);
    }

    public function testItGetsASocialAccountFromADifferentEndpoint()
    {
        $response = $this->actingAs($this->user, 'api')->json('GET', "/awesome-path/{$this->socialAccount->id}");

        $response
            ->assertStatus(200)
            ->assertJsonStructure(['data' => $this->structure])
            ->assertJson(['data' => [
                'id'               => $this->socialAccount->id,
                'provider'         => $this->socialAccount->provider,
                'provider_user_id' => $this->socialAccount->provider_user_id,
            ]]);
    }
}
