<?php

namespace audunru\SocialAccounts\DTOs;

class ProviderSettingsDto
{
    /**
     * @param array<string,string|int|bool|null>|null $parameters
     */
    public function __construct(public string $provider, public string $methodName, public ?array $parameters)
    {
    }
}
