<?php
use Ratchet\Server\IoServer;
use Ratchet\Http\HttpServer;
use Ratchet\WebSocket\WsServer;
use Draw\DrawSocket;

require dirname(__DIR__) . '/vendor/autoload.php';


$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new DrawSocket()
        )
    ),
    8080
);

$server->run();
