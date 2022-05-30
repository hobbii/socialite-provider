<?php

namespace Hobbii\SocialiteProvider;

use Laravel\Socialite\Two\User as SocialiteUser;

class User extends SocialiteUser
{
    public function getProvider(): string
    {
        return $this->user['provider'] ?? 'cognito';
    }

    public function isEmployee(): bool
    {
        return $this->getProvider() === 'google';
    }

    public function isMyHobbii(): bool
    {
        return $this->getProvider() === 'cognito';
    }
}
