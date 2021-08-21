<?php

use Psr\Http\Message\ServerRequestInterface;
use React\Http\HttpServer;
use React\Http\Message\Response;
use React\Socket\SocketServer;
use Sikei\React\Http\Middleware\CorsMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$server = new HttpServer(
    new CorsMiddleware(['server_url' => 'http://api.example.net:8080']),
    function (ServerRequestInterface $request) {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'some' => 'nice',
            'json' => 'values',
        ]));
    }
);

$socket = new SocketServer(isset($argv[1]) ? $argv[1] : '0.0.0.0:8080');
$server->listen($socket);

echo 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . PHP_EOL;
