<?php

use Psr\Http\Message\ServerRequestInterface;
use React\Http\HttpServer;
use React\Http\Message\Response;
use React\Socket\SocketServer;
use Sikei\React\Http\Middleware\CorsMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$server = new HttpServer(
    new CorsMiddleware(),
    function (ServerRequestInterface $request) {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'some' => 'nice',
            'json' => 'values',
        ]));
    }
);

$socket = new SocketServer(isset($argv[1]) ? $argv[1] : '0.0.0.0:0');
$server->listen($socket);

echo 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . PHP_EOL;