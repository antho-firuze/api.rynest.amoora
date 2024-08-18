<?php

namespace process;

use Exception;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\ServerSentEvents;
use Workerman\Protocols\Http\Response;
use Workerman\Timer;
use Firuze\Jwt\JwtToken;
use support\Db;

class EventSource
{
    public function onMessage(TcpConnection $connection, Request $request)
    {
        // Get value from Form-Data
        $data = $request->post();
        $stream_type = $data['stream_type'] ?? '';
        switch ($stream_type) {
            case 'online_host':
                $this->online_host_stream($connection, $request);
                break;

            case 'participant':
                $this->participant_stream($connection, $request);
                break;

            case 'audience':
                $this->audience_stream($connection, $request);
                break;

            case 'online_member':
                $this->online_member_stream($connection, $request);
                break;

            case 'notification':
                $this->notification($connection, $request);
                break;

            default:
                $connection->send(jsonr(['message' => 'stream_type: invalid']));
                break;
        }
    }

    function checkToken(TcpConnection $connection, Request $request): int
    {
        try {
            $authorization = $request->header('Authorization');
            if (!$authorization || 'undefined' == $authorization) {
                if (empty($authorization)) {
                    $connection->send(jsonr(['message' => 'Request the information that is not carried by Authorization']));
                }
            }
            [$type, $token] = explode(' ', $authorization);
            $result = JwtToken::verify(1, $token);

            // $this->user_id = $result['extend']['id'];
            return $result['extend']['id'];
        } catch (\Throwable $e) {
            $connection->send(jsonr(['message' => $e->getMessage()]));
        }
    }

    public function online_host_stream(TcpConnection $connection, Request $request)
    {
        // Check is Token Valid
        $user_id = $this->checkToken($connection, $request);
        // Init variables
        $old_value = '';

        // If the access head is Text/Event-Stream, it means that it is the SSE request
        if ($request->header('Accept') === 'text/event-stream') {
            // First send a response to Content-Type: Text/Event-Stream header
            $connection->send(new Response(200, ['Content-Type' => 'text/event-stream', 'Access-Control-Allow-Origin' => '*'], "\n\n"));

            // Push data to the client regularly
            $timer_id = Timer::add(2, function () use ($connection, $request, &$timer_id, &$old_value) {
                // When the connection is turned off, delete the timer to avoid the continuous accumulation of the timer and cause memory leakage.
                if ($connection->getStatus() !== TcpConnection::STATUS_ESTABLISHED) {
                    Timer::del($timer_id);
                    return;
                }

                // $new_value = $this->get_presenter($request);
                $presenters = Db::table('presenter')
                    ->where('state', '=', 'active')
                    ->get();

                foreach ($presenters as $key => $value) {
                    $presenters[$key]->profile = $this->get_profile($value->user_id);
                }

                $new_value = json_encode($presenters);
                if (strcmp($old_value, $new_value) !== 0) {
                    $connection->send(new ServerSentEvents(['event' => 'message', 'data' => $new_value, 'id' => time()]));
                    $old_value = $new_value;
                }
            });
        }
        return;
    }

    public function participant_stream(TcpConnection $connection, Request $request)
    {
        // Check is Token Valid
        $user_id = $this->checkToken($connection, $request);
        // Init variables
        $data = $request->post();
        if (!isset($data['presenter_id'])) {
            $connection->send(jsonr(['message' => 'presenter_id: invalid']));
            return;
        }
        $old_value = '';

        // If the access head is Text/Event-Stream, it means that it is the SSE request
        if ($request->header('Accept') === 'text/event-stream') {
            // First send a response to Content-Type: Text/Event-Stream header
            $connection->send(new Response(200, ['Content-Type' => 'text/event-stream', 'Access-Control-Allow-Origin' => '*'], "\n\n"));

            // Push data to the client regularly
            $timer_id = Timer::add(2, function () use ($connection, $request, &$timer_id, &$old_value) {
                // When the connection is turned off, delete the timer to avoid the continuous accumulation of the timer and cause memory leakage.
                if ($connection->getStatus() !== TcpConnection::STATUS_ESTABLISHED) {
                    Timer::del($timer_id);
                    return;
                }

                // $new_value = $this->get_audience($request);
                $data = $request->post();
                $audience = Db::table('audience')
                    ->where('presenter_id', $data['presenter_id'])
                    ->get();

                foreach ($audience as $key => $value) {
                    $audience[$key]->profile = $this->get_profile($value->user_id);
                }

                $new_value = json_encode($audience);
                if (strcmp($old_value, $new_value) !== 0) {
                    $connection->send(new ServerSentEvents(['event' => 'message', 'data' => $new_value, 'id' => time()]));
                    $old_value = $new_value;
                }
            });
        }
        return;
    }

    public function audience_stream(TcpConnection $connection, Request $request)
    {
        // Check is Token Valid
        $user_id = $this->checkToken($connection, $request);
        // Init variables
        $data = $request->post();
        if (!isset($data['id'])) {
            $connection->send(jsonr(['message' => 'id: invalid']));
            return;
        }
        $old_value = '';

        // If the access head is Text/Event-Stream, it means that it is the SSE request
        if ($request->header('Accept') === 'text/event-stream') {
            // First send a response to Content-Type: Text/Event-Stream header
            $connection->send(new Response(200, ['Content-Type' => 'text/event-stream', 'Access-Control-Allow-Origin' => '*'], "\n\n"));

            // Push data to the client regularly
            $timer_id = Timer::add(2, function () use ($connection, $request, &$timer_id, &$old_value) {
                // When the connection is turned off, delete the timer to avoid the continuous accumulation of the timer and cause memory leakage.
                if ($connection->getStatus() !== TcpConnection::STATUS_ESTABLISHED) {
                    Timer::del($timer_id);
                    return;
                }

                // $new_value = $this->get_audience($request);
                $data = $request->post();
                $audience = Db::table('audience')
                    ->where('id', $data['id'])
                    ->get();

                foreach ($audience as $key => $value) {
                    $audience[$key]->profile = $this->get_profile($value->user_id);
                }

                $new_value = json_encode($audience);
                if (strcmp($old_value, $new_value) !== 0) {
                    $connection->send(new ServerSentEvents(['event' => 'message', 'data' => $new_value, 'id' => time()]));
                    $old_value = $new_value;
                }
            });
        }
        return;
    }

    public function online_member_stream(TcpConnection $connection, Request $request)
    {
        // Check is Token Valid
        $user_id = $this->checkToken($connection, $request);
        // Init variables
        $old_value = '';

        // If the access head is Text/Event-Stream, it means that it is the SSE request
        if ($request->header('Accept') === 'text/event-stream') {
            // First send a response to Content-Type: Text/Event-Stream header
            $connection->send(new Response(200, ['Content-Type' => 'text/event-stream', 'Access-Control-Allow-Origin' => '*'], "\n\n"));

            // Push data to the client regularly
            $timer_id = Timer::add(2, function () use ($connection, $request, $user_id, &$timer_id, &$old_value) {
                    // When the connection is turned off, delete the timer to avoid the continuous accumulation of the timer and cause memory leakage.
                    if ($connection->getStatus() !== TcpConnection::STATUS_ESTABLISHED) {
                        Timer::del($timer_id);
                        return;
                    }

                    $users = Db::table('live_location')
                        ->where('user_id', '!=', $user_id)
                        ->get();

                    foreach ($users as $key => $value) {
                        $users[$key]->profile = $this->get_profile($value->user_id);
                    }

                    $new_value = json_encode($users);
                    if (strcmp($old_value, $new_value) !== 0) {
                        $connection->send(new ServerSentEvents(['event' => 'message', 'data' => $new_value, 'id' => time()]));
                        $old_value = $new_value;
                    }
                });
        }
        return;
    }

    public function notification(TcpConnection $connection, Request $request)
    {
        // Check is Token Valid
        $user_id = $this->checkToken($connection, $request);
        // Init variables
        $old_value = '';

        // If the access head is Text/Event-Stream, it means that it is the SSE request
        if ($request->header('Accept') === 'text/event-stream') {
            // First send a response to Content-Type: Text/Event-Stream header
            $connection->send(new Response(200, ['Content-Type' => 'text/event-stream', 'Access-Control-Allow-Origin' => '*'], "\n\n"));

            // Push data to the client regularly
            $timer_id = Timer::add(2, function () use ($connection, $request, $user_id, &$timer_id, &$old_value) {
                    // When the connection is turned off, delete the timer to avoid the continuous accumulation of the timer and cause memory leakage.
                    if ($connection->getStatus() !== TcpConnection::STATUS_ESTABLISHED) {
                        Timer::del($timer_id);
                        return;
                    }

                    $users = Db::table('notification')
                        ->where('user_id', '=', $user_id)
                        ->get();

                    $new_value = json_encode($users);
                    if (strcmp($old_value, $new_value) !== 0) {
                        $connection->send(new ServerSentEvents(['event' => 'message', 'data' => $new_value, 'id' => time()]));
                        $old_value = $new_value;
                    }
                });
        }
        return;
    }

    private function get_profile($user_id)
    {
        $user = Db::table('users')
            ->where('id', $user_id)
            ->first();
        $member = Db::table('members')
            ->where('user_id', $user_id)
            ->first();

        if (!$user && !$member) {
            return [];
        }

        return [
            'user_id' => $user->id,
            'member_id' => $member->id,
            'name' => $user->name,
            'email' => $user->email,
            'full_name' => $member->full_name,
            'phone' => $member->phone,
            'address' => $member->address,
            'photo' => $member->photo,
            'passport_no' => $member->passport_no,
        ];
    }
}
