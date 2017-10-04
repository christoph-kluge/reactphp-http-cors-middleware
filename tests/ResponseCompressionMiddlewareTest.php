<?php

namespace Sikei\React\Http\Middleware;

use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ServerRequestInterface;
use React\Http\Response;
use React\Http\ServerRequest;
use React\Promise\Promise;
use React\Promise\PromiseInterface;

class CorsMiddlewareTest extends TestCase
{

    public function testTemplate()
    {
        $request = new ServerRequest('GET', 'https://example.net/', ['Host' => 'example.net']);
        $response = new Response(200, ['Content-Type' => 'text/html'], 'Some response');

        $middleware = new CorsMiddleware();

        /** @var PromiseInterface $result */
        $result = $middleware($request, $this->getNextCallback($response));
        $result->then(function ($value) use (&$response) {
            $response = $value;
        });

        $this->assertNotNull($response);
        $this->assertInstanceOf('React\Http\Response', $response);
    }

    public function getNextCallback(Response $response)
    {
        return function (ServerRequestInterface $request) use (&$response) {
            return new Promise(function ($resolve, $reject) use ($request, &$response) {
                return $resolve($response);
            });
        };
    }
}
