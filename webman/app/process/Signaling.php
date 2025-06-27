<?php

namespace app\process;

use Workerman\Connection\TcpConnection;

class Signaling
{
    protected static $connection = [];

    public function onWebSocketConnect(TcpConnection $connection, $header)
    {
        $connection->send(json_encode(["message" => "onWebSocketConnect"]));
    }

    public function onMessage($connection, $data) 
    {
        $connection->send(json_encode($data));
    }

    public function onClose($connection)
    {
        $connection->send(json_encode(["message" => "onClose"]));
    }
}