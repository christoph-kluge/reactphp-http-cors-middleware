<?php

namespace Sikei\React\Http\Middleware;

use Neomerx\Cors\Contracts\Http\ParsedUrlInterface;
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
        $request = new ServerRequest('GET', 'https://api.example.net/');
        $response = new Response(200, ['Content-Type' => 'text/html'], 'Some response');

        $middleware = new CorsMiddleware([
            'allow_origin' => '*',
        ]);

        /** @var PromiseInterface $result */
        $result = $middleware($request, $this->getNextCallback($response));
        $this->assertInstanceOf('React\Promise\Promise', $result);

        $result->then(function ($value) use (&$response) {
            $response = $value;
        });
        $this->assertInstanceOf('React\Http\Response', $response);
    }

    public function testNoHostHeaderResponse()
    {
        $this->markTestSkipped('Not yet implemented');

        $request = new ServerRequest('OPTIONS', 'https://api.example.net/', [
            'Origin'                         => 'https://www.example.net',
            'Access-Control-Request-Method'  => 'GET',
            'Access-Control-Request-Headers' => 'Authorization',
        ]);
        $request = $request->withoutHeader('Host');
        $response = new Response(200, ['Content-Type' => 'text/html'], 'Some response');

        $middleware = new CorsMiddleware([
            'allow_origin'  => '*',
            'allow_methods' => ['OPTIONS'],
        ]);

        /** @var Response $result */
        $response = $middleware($request, $this->getNextCallback($response));
        $this->assertInstanceOf('React\Http\Response', $response);

        $this->assertSame(401, $response->getStatusCode());
    }

    public function testRequestInvalidRequestHeaders()
    {
        $request = new ServerRequest('OPTIONS', 'https://api.example.net/', [
            'Origin'                         => 'https://www.example.net',
            'Access-Control-Request-Method'  => 'GET',
            'Access-Control-Request-Headers' => 'Authorization',
        ]);
        $response = new Response(200, ['Content-Type' => 'text/html'], 'Some response');

        $middleware = new CorsMiddleware([
            'allow_origin'  => '*',
            'allow_headers' => [],
            'allow_methods' => ['GET', 'OPTIONS'],
        ]);

        /** @var Response $response */
        $response = $middleware($request, $this->getNextCallback($response));
        $this->assertInstanceOf('React\Http\Response', $response);
        $this->assertSame(401, $response->getStatusCode());
    }

    public function testRequestValidRequestHeaders()
    {
        $request = new ServerRequest('OPTIONS', 'https://api.example.net/', [
            'Origin'                         => 'https://www.example.net',
            'Access-Control-Request-Method'  => 'GET',
            'Access-Control-Request-Headers' => 'Authorization',
        ]);
        $response = new Response(200, ['Content-Type' => 'text/html'], 'Some response');

        $middleware = new CorsMiddleware([
            'allow_origin'  => '*',
            'allow_headers' => ['Authorization'],
            'allow_methods' => ['GET', 'OPTIONS'],
        ]);

        /** @var Response $response */
        $response = $middleware($request, $this->getNextCallback($response));
        $this->assertInstanceOf('React\Http\Response', $response);
        $this->assertSame(204, $response->getStatusCode());
    }

    public function testRequestInvalidMethods()
    {
        $request = new ServerRequest('OPTIONS', 'https://api.example.net/', [
            'Origin'                        => 'https://www.example.net',
            'Access-Control-Request-Method' => 'GET',
        ]);
        $response = new Response(200, ['Content-Type' => 'text/html'], 'Some response');

        $middleware = new CorsMiddleware([
            'allow_origin'  => '*',
            'allow_methods' => [],
        ]);

        /** @var Response $response */
        $response = $middleware($request, $this->getNextCallback($response));
        $this->assertInstanceOf('React\Http\Response', $response);
        $this->assertSame(405, $response->getStatusCode());
    }

    public function testRequestValidMethods()
    {
        $request = new ServerRequest('OPTIONS', 'https://api.example.net/', [
            'Origin'                        => 'https://www.example.net',
            'Access-Control-Request-Method' => 'GET',
        ]);
        $response = new Response(200, ['Content-Type' => 'text/html'], 'Some response');

        $middleware = new CorsMiddleware([
            'allow_origin'  => '*',
            'allow_methods' => ['GET', 'OPTIONS'],
        ]);

        /** @var Response $response */
        $response = $middleware($request, $this->getNextCallback($response));
        $this->assertInstanceOf('React\Http\Response', $response);
        $this->assertSame(204, $response->getStatusCode());
    }

    public function testRequestOriginByInvalidOrigin()
    {
        $request = new ServerRequest('OPTIONS', 'https://api.example.net/', [
            'Origin'                        => 'https://www.example.net',
            'Access-Control-Request-Method' => 'GET',
        ]);
        $response = new Response(200, ['Content-Type' => 'text/html'], 'Some response');

        $middleware = new CorsMiddleware([
            'allow_origin'  => [
                'https://invalid.example.net',
            ],
            'allow_methods' => ['GET', 'OPTIONS'],
        ]);

        /** @var Response $response */
        $response = $middleware($request, $this->getNextCallback($response));
        $this->assertInstanceOf('React\Http\Response', $response);
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testRequestOriginByValidOrigin()
    {
        $request = new ServerRequest('OPTIONS', 'https://api.example.net/', [
            'Origin'                        => 'https://www.example.net',
            'Access-Control-Request-Method' => 'GET',
        ]);
        $response = new Response(200, ['Content-Type' => 'text/html'], 'Some response');

        $middleware = new CorsMiddleware([
            'allow_origin'  => [
                'https://www.example.net',
            ],
            'allow_methods' => ['GET', 'OPTIONS'],
        ]);

        /** @var Response $response */
        $response = $middleware($request, $this->getNextCallback($response));
        $this->assertInstanceOf('React\Http\Response', $response);
        $this->assertSame(204, $response->getStatusCode());
    }

    public function testRequestOriginByWildcard()
    {
        $request = new ServerRequest('OPTIONS', 'https://api.example.net/', [
            'Origin'                        => 'https://www.example.net',
            'Access-Control-Request-Method' => 'GET',
        ]);
        $response = new Response(200, ['Content-Type' => 'text/html'], 'Some response');

        // -- test wildcard as string

        $middleware = new CorsMiddleware([
            'allow_origin'  => '*',
            'allow_methods' => ['GET', 'OPTIONS'],
        ]);

        /** @var Response $response */
        $response = $middleware($request, $this->getNextCallback($response));
        $this->assertInstanceOf('React\Http\Response', $response);
        $this->assertSame(204, $response->getStatusCode());

        // -- test wildcard as array

        $middleware = new CorsMiddleware([
            'allow_origin'  => ['*'],
            'allow_methods' => ['GET', 'OPTIONS'],
        ]);

        /** @var Response $response */
        $response = $middleware($request, $this->getNextCallback($response));
        $this->assertInstanceOf('React\Http\Response', $response);
        $this->assertSame(204, $response->getStatusCode());
    }

    public function testRequestOriginByPositiveCallback()
    {
        $request = new ServerRequest('OPTIONS', 'https://api.example.net/', [
            'Origin'                        => 'https://www.example.net',
            'Access-Control-Request-Method' => 'GET',
        ]);
        $response = new Response(200, ['Content-Type' => 'text/html'], 'Some response');

        // -- test positive callback

        $middleware = new CorsMiddleware([
            'allow_origin'          => [],
            'allow_origin_callback' => function (ParsedUrlInterface $parsedUrl) {
                return true;
            },
            'allow_methods'         => ['GET', 'OPTIONS'],
        ]);

        /** @var Response $response */
        $response = $middleware($request, $this->getNextCallback($response));
        $this->assertInstanceOf('React\Http\Response', $response);
        $this->assertSame(204, $response->getStatusCode());
    }

    public function testRequestOriginByNegativeCallback()
    {
        $request = new ServerRequest('OPTIONS', 'https://api.example.net/', [
            'Origin'                        => 'https://www.example.net',
            'Access-Control-Request-Method' => 'GET',
        ]);
        $response = new Response(200, ['Content-Type' => 'text/html'], 'Some response');

        $middleware = new CorsMiddleware([
            'allow_origin'          => [],
            'allow_origin_callback' => function (ParsedUrlInterface $parsedUrl) {
                return false;
            },
            'allow_methods'         => ['GET', 'OPTIONS'],
        ]);

        /** @var Response $response */
        $response = $middleware($request, $this->getNextCallback($response));
        $this->assertInstanceOf('React\Http\Response', $response);
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testRequestOriginByInvalidCallbackReturn()
    {
        $request = new ServerRequest('OPTIONS', 'https://api.example.net/', [
            'Origin'                        => 'https://www.example.net',
            'Access-Control-Request-Method' => 'GET',
        ]);
        $response = new Response(200, ['Content-Type' => 'text/html'], 'Some response');

        $middleware = new CorsMiddleware([
            'allow_origin'          => [],
            'allow_origin_callback' => function (ParsedUrlInterface $parsedUrl) {
                return null;
            },
            'allow_methods'         => ['GET', 'OPTIONS'],
        ]);

        /** @var Response $response */
        $response = $middleware($request, $this->getNextCallback($response));
        $this->assertInstanceOf('React\Http\Response', $response);
        $this->assertSame(403, $response->getStatusCode());
    }

    public function testRequestCustomPreflightMaxAge()
    {
        $request = new ServerRequest('OPTIONS', 'https://api.example.net/', [
            'Origin'                        => 'https://www.example.net',
            'Access-Control-Request-Method' => 'GET',
        ]);
        $response = new Response(200, ['Content-Type' => 'text/html'], 'Some response');

        $middleware = new CorsMiddleware([
            'allow_origin'  => '*',
            'allow_methods' => ['GET', 'OPTIONS'],
            'max_age'       => 3600,
        ]);

        /** @var Response $response */
        $response = $middleware($request, $this->getNextCallback($response));
        $this->assertInstanceOf('React\Http\Response', $response);
        $this->assertSame(204, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Access-Control-Max-Age'));
        $this->assertSame((string)3600, $response->getHeaderLine('Access-Control-Max-Age'));
    }

    public function testRequestCredentialsAllowed()
    {
        $request = new ServerRequest('OPTIONS', 'https://api.example.net/', [
            'Origin'                        => 'https://www.example.net',
            'Access-Control-Request-Method' => 'GET',
        ]);
        $response = new Response(200, ['Content-Type' => 'text/html'], 'Some response');

        $middleware = new CorsMiddleware([
            'allow_credentials' => true,
            'allow_origin'      => '*',
            'allow_methods'     => ['GET', 'OPTIONS'],
        ]);

        /** @var Response $response */
        $response = $middleware($request, $this->getNextCallback($response));
        $this->assertInstanceOf('React\Http\Response', $response);
        $this->assertSame(204, $response->getStatusCode());
        $this->assertTrue($response->hasHeader('Access-Control-Allow-Credentials'));
        $this->assertSame('true', strtolower($response->getHeaderLine('Access-Control-Allow-Credentials')));
    }

    public function testRequestCredentialsNotAllowed()
    {
        $request = new ServerRequest('OPTIONS', 'https://api.example.net/', [
            'Origin'                        => 'https://valid.example.net',
            'Access-Control-Request-Method' => 'GET',
        ]);
        $response = new Response(200, ['Content-Type' => 'text/html'], 'Some response');

        $middleware = new CorsMiddleware([
            'allow_credentials' => false,
            'allow_origin'      => '*',
            'allow_methods'     => ['GET', 'OPTIONS'],
        ]);

        /** @var Response $response */
        $response = $middleware($request, $this->getNextCallback($response));
        $this->assertInstanceOf('React\Http\Response', $response);
        $this->assertSame(204, $response->getStatusCode());
        $this->assertFalse($response->hasHeader('Access-Control-Allow-Credentials'));
    }

    public function testRequestCredentialsInvalidValueFallbackToFalse()
    {
        $request = new ServerRequest('OPTIONS', 'https://api.example.net/', [
            'Origin'                        => 'https://www.example.net',
            'Access-Control-Request-Method' => 'GET',
        ]);
        $response = new Response(200, ['Content-Type' => 'text/html'], 'Some response');

        $middleware = new CorsMiddleware([
            'allow_credentials' => new \stdClass(),
            'allow_origin'      => '*',
            'allow_methods'     => ['GET', 'OPTIONS'],
        ]);

        /** @var Response $response */
        $response = $middleware($request, $this->getNextCallback($response));
        $this->assertInstanceOf('React\Http\Response', $response);
        $this->assertSame(204, $response->getStatusCode());
        $this->assertFalse($response->hasHeader('Access-Control-Allow-Credentials'));
    }


    public function testRequestExposedHeaderForResponseShouldBeHidden()
    {
        $request = new ServerRequest('GET', 'https://api.example.net/', [
            'Origin'                        => 'https://valid.example.net',
            'Access-Control-Request-Method' => 'GET',
        ]);
        $response = new Response(200, ['Content-Type' => 'text/html'], 'Some response');

        $middleware = new CorsMiddleware([
            'allow_origin'   => '*',
            'allow_methods'  => ['GET'],
            'expose_headers' => [],
        ]);

        /** @var PromiseInterface $result */
        $result = $middleware($request, $this->getNextCallback($response));
        $result->then(function ($value) use (&$response) {
            $response = $value;
        });
        $this->assertInstanceOf('React\Http\Response', $response);

        $this->assertFalse($response->hasHeader('Access-Control-Expose-Headers'));
    }

    public function testRequestExposedHeaderForResponseShouldBeVisible()
    {
        $request = new ServerRequest('GET', 'https://api.example.net/', [
            'Origin'                        => 'https://www.example.net',
            'Access-Control-Request-Method' => 'GET',
        ]);
        $response = new Response(200, ['Content-Type' => 'text/html'], 'Some response');

        $middleware = new CorsMiddleware([
            'allow_origin'   => '*',
            'allow_methods'  => ['GET'],
            'expose_headers' => ['X-Custom-Header', 'X-Custom-Header-2'],
        ]);

        /** @var PromiseInterface $result */
        $result = $middleware($request, $this->getNextCallback($response));
        $result->then(function ($value) use (&$response) {
            $response = $value;
        });
        $this->assertInstanceOf('React\Http\Response', $response);

        $this->assertTrue($response->hasHeader('Access-Control-Expose-Headers'));
        $this->assertContains('X-Custom-Header', $response->getHeaderLine('Access-Control-Expose-Headers'));
        $this->assertContains('X-Custom-Header-2', $response->getHeaderLine('Access-Control-Expose-Headers'));
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
