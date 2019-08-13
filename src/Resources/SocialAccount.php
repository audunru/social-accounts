<?php

namespace audunru\SocialAccounts\Resources;

use Illuminate\Http\Resources\Json\Resource as JsonResource;

class SocialAccount extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return array
     */
    public function toArray($request): array
    {
        return [
            'id'               => $this->id,
            'provider'         => $this->provider,
            'provider_user_id' => $this->provider_user_id,
            'created_at'       => (string) $this->created_at,
            'updated_at'       => (string) $this->updated_at,
        ];
    }
}
