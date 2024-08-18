<?php

namespace app\api\controller;

use support\Request;
use support\Db;
use Firuze\Jwt\JwtToken;
use Webman\Push\Api;

class Pusher_v1
{
    protected $noNeedLogin = ['index'];

    public function index(Request $request)
    {
        return json(['message' => "Pusher API v1"]);
    }

    public function trigger(Request $request)
    {
        try {
            $api = new Api(
                // Under the webman, you can use config directly to get configuration.
                'http://127.0.0.1:3232',
                config('plugin.webman.push.app.app_key'),
                config('plugin.webman.push.app.app_secret')
            );
            // Message to send the MESSAGE event to all clients subscribed to User-1
            $data = [
                'from_uid' => 2,
                'content'  => 'Hello, this is the message content',
            ];
            $api->trigger('user-1', 'message', $data);

            return json($data);
        } catch (\Throwable $e) {
            return jsonr(['message' => $e->getMessage()]);
        }
    }

}
