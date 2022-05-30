<?php

namespace Tests;

use Faker\Factory;
use Faker\Generator;
use Hobbii\SocialiteProvider\HobbiiProvider;
use Hobbii\SocialiteProvider\User;
use Illuminate\Contracts\Session\Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Str;
use Orchestra\Testbench\TestCase;

class HobbiiProviderTest extends TestCase
{
    use WithGuzzle;

    private Generator $faker;
    private Request $request;
    private string $host;
    private string $clientId;
    private string $clientSecret;
    private string $redirectUrl;
    private HobbiiProvider $provider;

    protected function setUp(): void
    {
        parent::setUp();
        $this->faker = Factory::create();
        $this->request = new Request();
        $this->request->setLaravelSession($this->app->make(Session::class));
        $this->host = $this->faker->unique()->domainName();
        Config::set('hobbii-socialite.settings.host', "https://{$this->host}");
        $this->clientId = $this->faker->uuid();
        $this->clientSecret = $this->faker->password(32);
        $this->redirectUrl = "'https://{$this->faker->unique()->domainName()}/auth/callback'";

        $this->provider = new HobbiiProvider($this->request, $this->clientId, $this->clientSecret, $this->redirectUrl);
    }

    public function testCanGenerateAuthUrl(): void
    {
        $parts = parse_url($this->provider->redirect()->getTargetUrl());

        $this->assertEquals($this->host, $parts['host']);
        $this->assertEquals('/oauth/authorize', $parts['path']);
        $this->assertStringContainsStringIgnoringCase("client_id=$this->clientId", $parts['query']);
        $this->assertStringContainsStringIgnoringCase('redirect_uri=' . urlencode($this->redirectUrl), $parts['query']);
        $this->assertStringContainsStringIgnoringCase('response_type=code', $parts['query']);
        $this->assertStringContainsStringIgnoringCase("state={$this->request->session()->get('state')}", $parts['query']);
    }

    public function testCanGetAccessTokenResponse(): void
    {
        $response = $this->makeResponse($body = ['access_token' => $this->faker->password(255)]);
        $client = $this->makeClient($response);
        $this->provider->setHttpClient($client);

        $this->assertEquals($body, $this->provider->getAccessTokenResponse($code = $this->faker->password()));

        $request = $this->getRequest();

        $this->assertEquals(Request::METHOD_POST, $request->getMethod());
        $this->assertEquals($this->host, $request->getUri()->getHost());
        $this->assertEquals('/oauth/token', $request->getUri()->getPath());
        $this->assertEquals(http_build_query([
            'grant_type' => 'authorization_code',
            'client_id' => $this->clientId,
            'client_secret' => $this->clientSecret,
            'code' => $code,
            'redirect_uri' => $this->redirectUrl,
        ]), $request->getBody()->getContents());
    }

    public function testCanRetrieveUserByToken(): void
    {
        $state = $this->faker->password();
        $this->request->session()->put('state', $state);
        $this->request->replace([
            'state' => $state,
        ]);
        $hobbiiUser = [
            'cognito_id' => $this->faker->uuid(),
            'first_name' => $this->faker->firstName(),
            'last_name' => $this->faker->lastName(),
            'email' => $this->faker->email(),
            'provider' => $this->faker->randomElement(['cognito', 'google']),
            'locale' => Str::replace('_', '-', $this->faker->locale()),
        ];
        $accessTokenResponse = $this->makeResponse(['access_token' => $this->faker->password(255)]);
        $userResponse = $this->makeResponse($hobbiiUser);
        $this->provider->setHttpClient($this->makeClient([$accessTokenResponse, $userResponse]));
        $user = $this->provider->user();

        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals($hobbiiUser['cognito_id'], $user->getId());
        $this->assertEquals($hobbiiUser['first_name'], $user->getNickname());
        $this->assertEquals($hobbiiUser['first_name'] . ' ' . $hobbiiUser['last_name'], $user->getName());
        $this->assertEquals($hobbiiUser['email'], $user->getEmail());
        $this->assertEquals($hobbiiUser, $user->user);
        $this->assertEquals($hobbiiUser['provider'] === 'cognito', $user->isMyHobbii());
        $this->assertEquals($hobbiiUser['provider'] === 'google', $user->isEmployee());
    }
}
