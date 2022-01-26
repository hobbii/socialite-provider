<?php

namespace Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Illuminate\Support\Arr;

trait WithGuzzle
{
    private array $historyContainer = [];

    protected function makeClient(Response|RequestException|array $responses): Client
    {
        $mock = new MockHandler(Arr::wrap($responses));

        $handlerStack = HandlerStack::create($mock);

        $handlerStack->push(Middleware::history($this->historyContainer));

        return new Client(['handler' => $handlerStack]);
    }

    protected function makeResponse(string|array|object $body = null, array $headers = [], int $status = 200): Response
    {
        if (is_array($body) || is_object($body)) {
            $body = json_encode($body);
        }

        return new Response($status, $headers, $body);
    }

    protected function injectGuzzle(string $class, Response|RequestException|array $responses): void
    {
        $this->app->when($class)
            ->needs(Client::class)
            ->give(fn () => $this->makeClient($responses));
    }

    protected function getRequest(): ?Request
    {
        return Arr::get($this->historyContainer, '0.request');
    }

    protected function getResponse(): ?Response
    {
        return Arr::get($this->historyContainer, '0.response');
    }
}
