<?php

use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\Http\Response;
use React\Http\Server;
use React\Http\MiddlewareRunner;
use Sikei\React\Http\Middleware\CorsMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$loop = Factory::create();

$server = new Server(new MiddlewareRunner([
    new CorsMiddleware(),
    function (ServerRequestInterface $request, callable $next) {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'some' => 'nice',
            'json' => 'values',
        ]));
    },
]));

$socket = new \React\Socket\Server(isset($argv[1]) ? $argv[1] : '0.0.0.0:0', $loop);
$server->listen($socket);

echo 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . PHP_EOL;

$loop->run();
