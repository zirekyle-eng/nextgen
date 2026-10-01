<?php

namespace App\Entities;

use App\User;
use League\OAuth2\Server\Entities\Traits\EntityTrait;
use OpenIDConnect\Claims\Traits\WithClaims;
use OpenIDConnect\Interfaces\IdentityEntityInterface;

class IdentityEntity implements IdentityEntityInterface
{
    use EntityTrait, WithClaims;

    public function getClaims(): array
    {
        $user = User::find($this->getIdentifier());
        if (!$user) {
            return [];
        }

        return [
            'email' => $user->email,
            'name' => $user->name,
            'preferred_username' => $user->username ?? $user->email,
        ];
    }
}
