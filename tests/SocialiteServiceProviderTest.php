<?php

namespace Tests;

use Hobbii\SocialiteProvider\HobbiiProvider;
use Hobbii\SocialiteProvider\SocialiteServiceProvider;
use Laravel\Socialite\Facades\Socialite;
use Laravel\Socialite\SocialiteServiceProvider as SocialiteProvider;
use Orchestra\Testbench\TestCase;

class SocialiteServiceProviderTest extends TestCase
{
    protected function getPackageProviders($app)
    {
        return [
            SocialiteProvider::class,
            SocialiteServiceProvider::class,
        ];
    }

    public function testSocialiteIsExtended()
    {
        $this->assertInstanceOf(HobbiiProvider::class, Socialite::driver('hobbii'));
    }
}
