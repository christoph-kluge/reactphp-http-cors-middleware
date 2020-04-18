<?php

use Psr\Http\Message\ServerRequestInterface;
use React\EventLoop\Factory;
use React\Http\Response;
use React\Http\Server;
use Sikei\React\Http\Middleware\CorsMiddleware;

require __DIR__ . '/../vendor/autoload.php';

$loop = Factory::create();

$server = new Server([
    new CorsMiddleware(['server_url' => 'http://api.example.net:8080']),
    function (ServerRequestInterface $request) {
        return new Response(200, ['Content-Type' => 'application/json'], json_encode([
            'some' => 'nice',
            'json' => 'values',
        ]));
    },
]);

$socket = new \React\Socket\Server(isset($argv[1]) ? $argv[1] : '0.0.0.0:8080', $loop);
$server->listen($socket);

echo 'Listening on ' . str_replace('tcp:', 'http:', $socket->getAddress()) . PHP_EOL;

$loop->run();
