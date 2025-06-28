<?php

namespace app\process;

use Exception;
use Workerman\Connection\TcpConnection;
use Workerman\Protocols\Http\Request;
use Workerman\Protocols\Http\ServerSentEvents;
use Workerman\Protocols\Http\Response;
use Workerman\Timer;
use Firuze\Jwt\JwtToken;
use support\Db;
use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\NestedValidationException;
use support\MyFunc;
use Webman\Push\Api;

class Sse
{
    protected $app_key = '330f6a0a37d5d14357fe136b3ea11e06';

    protected $validatorDesc = [
        'attribute' => 'Params [{{name}}] is required',
        'stringType' => '[{{name}}] must be a string type',
        'intType' => '[{{name}}] must be integer',
        'email' => '[{{name}}] must be a valid email',
        'boolType' => '[{{name}}] must be a boolean type',
        'length' => '[{{name}}] length must be between {{minValue}} and {{maxValue}}',
        'number' => '[{{name}}] must be a number',
        'notEmpty' => '[{{name}}] must not empty',
        'noWhitespace' => '[{{name}}|username] cannot contain spaces',
    ];

    protected $noNeedLogin = [];
    // protected $noNeedLogin = ['online_streamer', 'get_viewers_count', 'get_streamer_status'];

    public $user_id = null;

    /**
     * GET /sse/my-stream?duration=5
     *
     */
    public function onMessage(TcpConnection $connection, Request $request)
    {
        $this->user_id = null;

        // Request path filter
        $path = $request->path();
        $explode = explode('/', trim($path, '/'));
        if (count($explode) < 2) {
            $connection->send(jsonr(['message' => 'Bad Request'], 400));
        }

        // Method requires token
        $type = $explode[1];
        if (!in_array($type, $this->noNeedLogin)) {
            $this->verifyToken($connection, $request);
        }

        switch ($type) {
            case 'online_streamer':
                $this->online_streamer($connection, $request);
                break;

            case 'get_viewers_count':
                $this->get_viewers_count($connection, $request);
                break;

            case 'get_streamer_status':
                $this->get_streamer_status($connection, $request);
                break;

            case 'notification':
                $this->notification($connection, $request);
                break;

            default:
                $connection->send(jsonr(['message' => 'stream_type: invalid']));
                break;
        }
    }

    function verifyToken(TcpConnection $connection, Request $request)
    {
        try {
            $authorization = $request->header('Authorization');
            if (!$authorization || 'undefined' == $authorization) {
                if (empty($authorization)) {
                    $connection->send(jsonr(['message' => 'Request the information that is not carried by Authorization'], 401));
                }
            }
            [$type, $token] = explode(' ', $authorization);
            if ($token != $this->app_key) {
                throw new Exception("Request Authorization Token failed");
            }

            // $result = JwtToken::verify(1, $token);
            // $this->user_id = $result['extend']['id'];
        } catch (\Throwable $e) {
            $connection->send(jsonr(['message' => $e->getMessage()], 403));
        }
    }

    /**
     * GET http://192.168.18.234:8686/sse/online_streamer
     *
     */
    public function online_streamer(TcpConnection $connection, Request $request)
    {
        // FIRST STAGE (Parameters)
        // ========================
        $param = $request->get();
        // try {
        //     $inputValidator = v::attribute('duration', v::notEmpty());
        //     $inputValidator->assert((object) $param);
        // } catch (NestedValidationException $e) {
        //     $errAttr = $e->getMessages($this->validatorDesc);
        //     $errMessage = join(", ", (array) $errAttr['attribute']);
        //     $connection->send(jsonr(['message' => $errMessage]));
        //     return;
        // }
        $duration = $param['duration'] ?? 2;  // Duration in second
        $old_value = '';

        if ($request->header('Accept') !== 'text/event-stream') {
            $connection->send(jsonr(['message' => 'Request Header not satisfying !'], 400));
        }

        // First send a response to Content-Type: Text/Event-Stream header
        $connection->send(new Response(200, ['Content-Type' => 'text/event-stream', 'Access-Control-Allow-Origin' => '*'], "\n\n"));

        // MIDDLE STAGE (Main Process) - Push data to the client regularly
        // ===============================================================
        $timer_id = Timer::add($duration, function () use ($connection, $request, &$timer_id, &$old_value) {
            // When the connection is turned off, delete the timer to avoid the continuous accumulation of the timer and cause memory leakage.
            if ($connection->getStatus() !== TcpConnection::STATUS_ESTABLISHED) {
                Timer::del($timer_id);
                return;
            }

            try {
                $rows = Db::table('streamer')
                    ->selectRaw('id, user_id, title, description, note, started_at, live_views, signaling_channel, streaming_channel')
                    ->where('finished_at', '=', null)
                    ->get();

                $result = $rows;

                $new_value = json_encode($result);
                if (strcmp($old_value, $new_value) !== 0) {
                    $connection->send(new ServerSentEvents(['event' => 'message', 'data' => $new_value, 'id' => time()]));
                    $old_value = $new_value;
                }
            } catch (\Throwable $th) {
                $connection->send(new ServerSentEvents(['event' => 'error', 'data' => $th->getMessage(), 'id' => time()]));
                throw $th;
            }
        });
    }

    /**
     * GET http://192.168.18.234:8686/sse/get_viewers_count?channel=live-streaming-13
     *
     */
    public function get_viewers_count(TcpConnection $connection, Request $request)
    {
        // FIRST STAGE (Parameters)
        // ========================
        $param = $request->get();
        try {
            $inputValidator = v::attribute('channel', v::notEmpty());
            $inputValidator->assert((object) $param);
        } catch (NestedValidationException $e) {
            $errAttr = $e->getMessages($this->validatorDesc);
            $errMessage = join(", ", (array) $errAttr['attribute']);
            $connection->send(jsonr(['message' => $errMessage]));
            return;
        }
        $duration = $param['duration'] ?? 2;  // Duration in second
        $old_value = '';

        if ($request->header('Accept') !== 'text/event-stream') {
            $connection->send(jsonr(['message' => 'Request Header not satisfying !'], 400));
        }

        // First send a response to Content-Type: Text/Event-Stream header
        $connection->send(new Response(200, ['Content-Type' => 'text/event-stream', 'Access-Control-Allow-Origin' => '*'], "\n\n"));

        // MIDDLE STAGE (Main Process) - Push data to the client regularly
        // ===============================================================
        $timer_id = Timer::add($duration, function () use ($connection, $param, &$timer_id, &$old_value) {
            // When the connection is turned off, delete the timer to avoid the continuous accumulation of the timer and cause memory leakage.
            if ($connection->getStatus() !== TcpConnection::STATUS_ESTABLISHED) {
                Timer::del($timer_id);
                return;
            }

            try {
                $channelName = $param['channel'];
                $res = MyFunc::getChannels($channelName);
                $channels = $res['result']['channels'];
                $row = [];
                foreach ($channels as $key => $value) {
                    $row['channel'] = $key;
                    $row['live_views'] = $value['subscription_count'] - 1;
                }
                $result = $row;

                $new_value = json_encode($result);
                if (strcmp($old_value, $new_value) !== 0) {
                    // Update live viewers count
                    Db::table('streamer')
                        ->where('streaming_channel', '=', $param['channel'])
                        ->update(['live_views' => $result['live_views'] ?? 0]);

                    $connection->send(new ServerSentEvents(['event' => 'message', 'data' => $new_value, 'id' => time()]));
                    $old_value = $new_value;
                }
            } catch (\Throwable $th) {
                $connection->send(new ServerSentEvents(['event' => 'error', 'data' => $th->getMessage(), 'id' => time()]));
                throw $th;
            }

        });
    }

    /**
     * GET http://192.168.18.234:8686/sse/get_streamer_status?id=13
     *
     */
    public function get_streamer_status(TcpConnection $connection, Request $request)
    {
        // FIRST STAGE (Parameters)
        // ========================
        $param = $request->get();
        try {
            $inputValidator = v::attribute('id', v::notEmpty());
            $inputValidator->assert((object) $param);
        } catch (NestedValidationException $e) {
            $errAttr = $e->getMessages($this->validatorDesc);
            $errMessage = join(", ", (array) $errAttr['attribute']);
            $connection->send(jsonr(['message' => $errMessage]));
            return;
        }
        $duration = $param['duration'] ?? 2;  // Duration in second
        $old_value = '';

        if ($request->header('Accept') !== 'text/event-stream') {
            $connection->send(jsonr(['message' => 'Request Header not satisfying !'], 400));
        }

        // First send a response to Content-Type: Text/Event-Stream header
        $connection->send(new Response(200, ['Content-Type' => 'text/event-stream', 'Access-Control-Allow-Origin' => '*'], "\n\n"));

        // MIDDLE STAGE (Main Process) - Push data to the client regularly
        // ===============================================================
        $timer_id = Timer::add($duration, function () use ($connection, $param, &$timer_id, &$old_value) {
            // When the connection is turned off, delete the timer to avoid the continuous accumulation of the timer and cause memory leakage.
            if ($connection->getStatus() !== TcpConnection::STATUS_ESTABLISHED) {
                Timer::del($timer_id);
                return;
            }

            try {
                $row = Db::table('streamer')
                    ->where('id', '=', $param['id'])
                    ->first();

                $result = $row;

                $new_value = json_encode($result);
                if (strcmp($old_value, $new_value) !== 0) {
                    $connection->send(new ServerSentEvents(['event' => 'message', 'data' => $new_value, 'id' => time()]));
                    $old_value = $new_value;
                }
            } catch (\Throwable $th) {
                $connection->send(new ServerSentEvents(['event' => 'error', 'data' => $th->getMessage(), 'id' => time()]));
                throw $th;
            }

        });
    }

    public function notification(TcpConnection $connection, Request $request)
    {
        // Check is Token Valid
        $user_id = $this->verifyToken($connection, $request);
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
}
