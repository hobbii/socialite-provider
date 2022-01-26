<?php

namespace Hobbii\SocialiteProvider;

use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Laravel\Socialite\Two\AbstractProvider;
use Laravel\Socialite\Two\ProviderInterface;
use Laravel\Socialite\Two\User;

class HobbiiProvider extends AbstractProvider implements ProviderInterface
{
    /**
     * @inheritDoc
     */
    protected function getAuthUrl($state): string
    {
        return $this->buildAuthUrlFromBase(
            $this->getUrl('oauth/authorize'),
            $state
        );
    }

    /**
     * @inheritDoc
     */
    protected function getTokenUrl(): string
    {
        return $this->getUrl('oauth/token');
    }

    /**
     * @inheritDoc
     * @throws \JsonException
     * @throws GuzzleException
     */
    protected function getUserByToken($token): array
    {
        $response = $this->getHttpClient()->get(
            $this->getUrl('api/user'),
            [
                RequestOptions::HEADERS => [
                    'Authorization' => "Bearer $token",
                ],
            ]
        );

        return (array) json_decode($response->getBody()->getContents(), associative: true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * @inheritDoc
     * @throws \Throwable
     */
    protected function mapUserToObject(array $user): User
    {
        throw_if(empty($user), \Exception::class, 'No user returned!');

        return (new User())->setRaw($user)->map([
            'id' => Arr::get($user, 'cognito_id', Arr::get($user, 'google_id')),
            'nickname' => Arr::get($user, 'first_name'),
            'name' => Arr::get($user, 'first_name') . ' ' . Arr::get($user, 'last_name'),
            'email' => Arr::get($user, 'email'),
        ]);
    }

    private function getUrl(string $path): string
    {
        return Str::finish(Config::get('hobbii-socialite.settings.host'), '/') . $path;
    }
}
