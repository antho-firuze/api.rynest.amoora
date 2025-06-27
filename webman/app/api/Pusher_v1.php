<?php

namespace app\api;

use support\Request;
use support\Db;
use Firuze\Jwt\JwtToken;
use support\MyFunc;
use Webman\Push\Api;
use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\NestedValidationException;

class Pusher_v1
{
    protected $noNeedLogin = ['index', 'auth'];

    protected $validatorDesc = [
        'attribute' => 'Params [{{name}}] is required',
        'stringType' => '[{{name}}] must be a string type',
        'email' => '[{{name}}] must be a valid email',
        'boolType' => '[{{name}}] must be a boolean type',
        'notEmpty' => '[{{name}}] must not empty',
    ];

    public function index(Request $request)
    {
        return json(['message' => "Pusher API v1"]);
    }

    public function auth(Request $request)
    {
        $pusher = new Api(str_replace('0.0.0.0', '127.0.0.1', config('plugin.webman.push.app.api')), config('plugin.webman.push.app.app_key'), config('plugin.webman.push.app.app_secret'));
        $channel_name = $request->post('channel_name');
        $socket_id = $request->post('socket_id');
        $user_id = $request->post('user_id');
        $user_info = $request->post('user_info');
        $session = $request->session();
        // Here, we should use session and channel_name to determine whether the current user has permission to listen to channel_name.
        $has_authority = true;
        if ($has_authority) {
            if (strpos($channel_name, 'private-') === 0) {
                $result = $pusher->socketAuth($channel_name, $socket_id);
                return response($result);
            } elseif (strpos($channel_name, 'presence-') === 0) {
                $result = $pusher->presenceAuth($channel_name, $socket_id, $user_id, $user_info);
                return response($result);
            }
        } else {
            return response('Forbidden', 403);
        }
    }

    // This trigger is open for all event, but must be authorized first.
    // Example:
    // {
    //     "channel_name": "private-user1",
    //     "socket_id": 1.151,
    //     "event": "client-message",
    //     "data": {
    //         "message": "hello"
    //     }
    // }
    public function trigger_all(Request $request)
    {
        // FIRST STAGE (Parameters)
        // ========================
        $data = $request->post();
        try {
            $inputValidator = v::attribute('channel_name', v::notEmpty())
                ->attribute('event', v::notEmpty())
                ->attribute('data', v::notEmpty());
            $inputValidator->assert((object) $data);
        } catch (NestedValidationException $e) {
            $errAttr = $e->getMessages($this->validatorDesc);
            $errMessage = join(", ", (array) $errAttr['attribute']);
            return jsonr(['message' => $errMessage]);
        }

        // MIDDLE STAGE (Main Process)
        // ===========================
        try {
            $channel_name = $data['channel_name'];
            $event = $data['event'];
            $data = $data['data'];
            $socket_id = $data['socket_id'] ?? '';
            $res = MyFunc::send_notif($channel_name, $event, $data, $socket_id);
                
            $result['message'] = $res ? 'done' : 'failed';

        } catch (\Throwable $th) {
            return jsonr(["message" => $th->getMessage(), "trace" => $th->getTrace()]);
        }

        // LAST STAGE (Output Process)
        // ===========================
        return json($result);
    }

    // {
    //     "filter_by_prefix": ""
    // }
    public function channels(Request $request)
    {
        // FIRST STAGE (Parameters)
        // ========================
        $data = $request->post();

        // MIDDLE STAGE (Main Process)
        // ===========================
        try {
            $filter = $data['filter_by_prefix'] ?? '';
            $res = MyFunc::getChannels($filter);

            $channels = $res['result']['channels'];
            $raw = [];
            $count = 0;
            foreach ($channels as $key => $value) {
                $explode = explode('-', $key);
                $raw[$count]['channel'] = $key;
                $raw[$count]['subscription_count'] = $value['subscription_count'];
                $count++;
            }

            $result = $raw;
        } catch (\Throwable $th) {
            return jsonr(["message" => $th->getMessage(), "trace" => $th->getTrace()]);
        }

        // LAST STAGE (Output Process)
        // ===========================
        return json($result);
    }

    // {
    //     "channel_name": "private-user1"
    // }
    public function channel_info(Request $request)
    {
        // FIRST STAGE (Parameters)
        // ========================
        $data = $request->post();
        try {
            $inputValidator = v::attribute('channel_name', v::notEmpty());
            $inputValidator->assert((object) $data);
        } catch (NestedValidationException $e) {
            $errAttr = $e->getMessages($this->validatorDesc);
            $errMessage = join(", ", (array) $errAttr['attribute']);
            return jsonr(['message' => $errMessage]);
        }

        // MIDDLE STAGE (Main Process)
        // ===========================
        try {
            $channel_name = $data['channel_name'] ?? '';
            $res = MyFunc::getChannels($channel_name);

            if (count($res['result']['channels']) < 1) {
                $result = [];
            } else {
                $result = $res['result']['channels'][$channel_name];
            }
        } catch (\Throwable $th) {
            return jsonr(["message" => $th->getMessage(), "trace" => $th->getTrace()]);
        }
        
        // LAST STAGE (Output Process)
        // ===========================
        return json($result);
    }

    // {
    //     "channel_name": "live-streaming-b002276d-6737-48b7-aab1-ac5a9ab23028"
    // }    
    public function channel_info_users(Request $request)
    {
        // FIRST STAGE (Parameters)
        // ========================
        $data = $request->post();
        try {
            $inputValidator = v::attribute('channel_name', v::notEmpty());
            $inputValidator->assert((object) $data);
        } catch (NestedValidationException $e) {
            $errAttr = $e->getMessages($this->validatorDesc);
            $errMessage = join(", ", (array) $errAttr['attribute']);
            return jsonr(['message' => $errMessage]);
        }

        // MIDDLE STAGE (Main Process)
        // ===========================
        try {
            $channel_name = $data['channel_name'] ?? '';
            $res = MyFunc::channelInfoUsers($channel_name);

            $result = $res['result'];
        } catch (\Throwable $th) {
            return jsonr(["message" => $th->getMessage(), "trace" => $th->getTrace()]);
        }

        // LAST STAGE (Output Process)
        // ===========================
        return json($result);
    }
}
