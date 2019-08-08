<?php

namespace audunru\SocialAccounts\Tests\Feature;

use audunru\SocialAccounts\Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;

class RouteTest extends TestCase
{
    use WithFaker;

    protected $provider = 'google';

    public function test_disabled_provider_has_no_route()
    {
        $this->get('/social/login/twitter')
          ->assertStatus(404);
    }

    public function test_it_redirects_from_login_to_provider()
    {
        config(["services.{$this->provider}.client_id" => $this->faker->uuid]);
        config(["services.{$this->provider}.client_secret" => $this->faker->uuid]);
        config(["services.{$this->provider}.redirect" => $this->faker->url]);

        $response = $this->get("/social/login/{$this->provider}");

        $response->assertStatus(302);
        $this->assertStringStartsWith('https://accounts.google.com/o/oauth2/auth', $response->getTargetUrl());
    }
}
