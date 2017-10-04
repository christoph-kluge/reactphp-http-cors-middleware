# ReactPHP Cors Middleware

This middleware implements [Cross-origin resource sharing](https://en.wikipedia.org/wiki/Cross-origin_resource_sharing) for ReactPHP. This repository got mainly inspired by [tuupola/cors-middleware](https://github.com/tuupola/cors-middleware).
Additional configuration ideas got taken from [barryvdh/laravel-cors](https://github.com/barryvdh/laravel-cors) and [nelmio/NelmioCorsBundle](https://github.com/nelmio/NelmioCorsBundle). The internal heavy lifting is done by [neomerx/cors-psr7](https://github.com/neomerx/cors-psr7) library.

[![Build Status](https://travis-ci.org/christoph-kluge/reactphp-http-cors-middleware.svg?branch=master)](https://travis-ci.org/christoph-kluge/reactphp-http-cors-middleware)
[![Total Downloads](https://poser.pugx.org/christoph-kluge/reactphp-http-cors-middleware/downloads)](https://packagist.org/packages/christoph-kluge/reactphp-http-cors-middleware)
[![License](https://poser.pugx.org/christoph-kluge/reactphp-http-cors-middleware/license)](https://packagist.org/packages/christoph-kluge/reactphp-http-cors-middleware)

# Install

To install via [Composer](http://getcomposer.org/), use the command below, it will automatically detect the latest version and bind it with `^`.

```
composer require christoph-kluge/reactphp-http-cors-middleware
```

This middleware will detect CORS requests and will intercept the request if there is something invalid.

# Usage

```php
$server = new Server(new MiddlewareRunner([
    new CorsMiddleware(),
    function (ServerRequestInterface $request, callable $next) {
        return new Response(200, ['Content-Type' => 'text/html'], 'We test CORS');
    },
]));
```

# Configuration

The defaults for this middleware are mainly taken from [enable-cors.org](https://enable-cors.org).

## Available configuration options

Thanks to [expressjs/cors#configuring-cors](https://github.com/expressjs/cors#configuring-cors). As I took most configuration descriptions from there.

* `response_code`: can be used to set the HTTP-StatusCode on a successful `OPTIONS` / Pre-Flight-Request (default: `204`)
* `allow_credentials`: Configures the `Access-Control-Allow-Credentials` CORS header. Expects an boolean (ex: `true` // to set the header)
* `allow_origin`: Configures the `Access-Control-Allow-Origin` CORS header. Expects an array (ex: `['http://example.net', 'https://example.net']`).
* `allow_origin_callback`: Will set `allow_origin` to an empty array `[]` and use the callback on a per-request base. The first parameter is an instance of `ParsedUrlInterface` and the callback is expected to return an `boolean`.
* `allow_methods`: Configures the `Access-Control-Allow-Methods` CORS header. Expects an array (ex: `['GET', 'PUT', 'POST']`).
* `allow_headers`: Configures the `Access-Control-Allow-Headers` CORS header. Expects an array (ex: `['Content-Type', 'Authorization']`).
* `expose_headers`: Configures the `Access-Control-Expose-Headers` CORS header. Expects an array (ex: `['Content-Range', 'X-Content-Range']`).
* `max_age`: Configures the `Access-Control-Max-Age` CORS header. Expects an integer representing seconds (ex: `1728000` // 20 days)

## Default Settings (Allow All CORS Requests)

```php
$settings = [
    'allow_credentials' => true,
    'allow_origin'      => ['*'],
    'allow_methods'     => ['GET', 'POST', 'PUT', 'DELETE', 'HEAD', 'OPTIONS'],
    'allow_headers'     => ['DNT','X-Custom-Header','Keep-Alive','User-Agent','X-Requested-With','If-Modified-Since','Cache-Control','Content-Type','Content-Range','Range'],
    'expose_headers'    => ['DNT','X-Custom-Header','Keep-Alive','User-Agent','X-Requested-With','If-Modified-Since','Cache-Control','Content-Type','Content-Range','Range'],
    'max_age'           => 60 * 60 * 24 * 20, // preflight request is valid for 20 days
];
```

## Allow specific origins (Origin requires scheme, host and optionally port)

```php
$server = new Server(new MiddlewareRunner([
    new CorsMiddleware([
        'allow_origin' => [
            'http://www.example.net',
            'https://www.example.net',
            'http://www.example.net:8443',
        ],
    ]),
]));
```

## Allow origins on a per-request base (callback)

```php
$server = new Server(new MiddlewareRunner([
    new CorsMiddleware([
        'allow_origin'          => [],
        'allow_origin_callback' => function(ParsedUrlInterface $parsedUrl) {
            $remoteHostName = $parsedUrl->getHost();
            $allowedTopLevelDomain = 'example.net';

            return substr(
                $remoteHostName,
                strlen($remoteHostName) - strlen($allowedTopLevelDomain)
            ) == $allowedTopLevelDomain;
        },
    ]),
]));
```

## Use custom response code on pre-flight requests (some legacy browsers choke on 204)

Thanks to [expressjs/cors#configuring-cors](https://github.com/expressjs/cors#configuring-cors)

```php
$server = new Server(new MiddlewareRunner([
    new CorsMiddleware([
        'allow_origin'      => [
            'http://www.my-website.de',
            'https://www.my-website.de',
            'http://www.my-website.de:8443',
        ],
    ]),
]));
```

# License

The MIT License (MIT)

Copyright (c) 2017 Christoph Kluge

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
