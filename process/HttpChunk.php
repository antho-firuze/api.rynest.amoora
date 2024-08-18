<?php

namespace process;

use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\Chunk;
use Workerman\Protocols\Http\Response;
use Workerman\Timer;

class HttpChunk
{
    public function onMessage(TcpConnection $connection, Request $request)
    {
        // First, send a response response with Transfer-Endcoding: chunket header
        $total_count = 10;
        $connection->send(new Response(200, array('Transfer-Encoding' => 'chunked'), "common {$total_count}Paragraph data<br>"));
        $timer_id = Timer::add(2, function () use ($connection, &$timer_id, $total_count) {
            static $count = 0;
            // When the connection is turned off, delete the timer to avoid the continuous accumulation of the timer and cause memory leakage.
            if ($connection->getStatus() !== TcpConnection::STATUS_ESTABLISHED) {
                Timer::del($timer_id);
                return;
            }
            if ($count++ >= $total_count) {
                // Send an empty '' representative to end the response
                $connection->send(new Chunk(''));
                return;
            }
            // Send chunk data
            $connection->send(new Chunk("First {$count} Paragraph data<br>"));
        });
    }
}
