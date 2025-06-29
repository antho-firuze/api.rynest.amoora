<?php

namespace app\api;

use support\Request;
use support\Db;
use Firuze\Jwt\JwtToken;
use support\Response;
use Webman\Push\Api;
use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\NestedValidationException;
use support\MyFunc;

class Streamer_v1
{
    protected $noNeedLogin = ['index', 'start', 'stop', 'heartbeat', 'all', 'byid', 'join', 'leave', 'push_send', 'ice_server_config'];

    protected $validatorDesc = [
        'attribute' => 'Params [{{name}}] is required',
        'stringType' => '[{{name}}] must be a string type',
        'email' => '[{{name}}] must be a valid email',
        'boolType' => '[{{name}}] must be a boolean type',
        'notEmpty' => '[{{name}}] must not empty',
    ];

    public function index(Request $request)
    {
        return json(['message' => "Streamer API v1"]);
    }

    /**
     * Get list online streamers
     *
     * @return	json 
     */
    // public function all(Request $request)
    // {
    //     // FIRST STAGE (Parameters)
    //     // ========================
    //     $data = $request->post();

    //     // MIDDLE STAGE (Main Process)
    //     // ===========================
    //     // Db::beginTransaction();
    //     try {
    //         $pusher = new Api(str_replace('0.0.0.0', '127.0.0.1', config('plugin.webman.push.app.api')), config('plugin.webman.push.app.app_key'), config('plugin.webman.push.app.app_secret'));
    //         $params = ["info" => "user_count,subscription_count", "filter_by_prefix" => "live-streaming"];
    //         $result = $pusher->getChannels($params);

    //         // Db::commit();
    //     } catch (\Throwable $th) {
    //         // Db::rollBack();
    //         return jsonr(["message" => $th->getMessage(), "trace" => $th->getTrace()]);
    //     }

    //     // LAST STAGE (Output Process)
    //     // ===========================
    //     return json($result);
    // }

    /**
     * Get specific online streamer by Id and with the subscriptions
     *
     * @param	string $id  could be int|uuid
     * @return	json 
     */
    public function byid(Request $request)
    {
        // FIRST STAGE (Parameters)
        // ========================
        $data = $request->post();
        try {
            // $inputValidator = v::attribute('id', v::intType()->notEmpty());
            $inputValidator = v::attribute('id', v::notEmpty());
            $inputValidator->assert((object) $data);
        } catch (NestedValidationException $e) {
            $errAttr = $e->getMessages($this->validatorDesc);
            $errMessage = join(", ", (array) $errAttr['attribute']);
            return jsonr(['message' => $errMessage]);
        }

        // MIDDLE STAGE (Main Process)
        // ===========================
        Db::beginTransaction();
        try {
            $row = Db::table('streamer')
                ->where('id', '=', $data['id'])
                ->first();
            $result = $row;

            Db::commit();
        } catch (\Throwable $th) {
            Db::rollBack();
            return jsonr(["message" => $th->getMessage(), "trace" => $th->getTrace()]);
        }

        // LAST STAGE (Output Process)
        // ===========================
        return json($result);
    }

    public function start(Request $request)
    {
        // FIRST STAGE (Parameters)
        // ========================
        $data = $request->post();
        try {
            $inputValidator = v::attribute('title', v::stringType()->notEmpty());
            $inputValidator->assert((object) $data);
        } catch (NestedValidationException $e) {
            $errAttr = $e->getMessages($this->validatorDesc);
            $errMessage = join(", ", (array) $errAttr['attribute']);
            return jsonr(['message' => $errMessage]);
        }

        // MIDDLE STAGE (Main Process)
        // ===========================
        Db::beginTransaction();
        try {
            $res = [
                'user_id' => $data['user_id'] ?? null,
                'title' => $data['title'],
                'description' => $data['description'] ?? null,
                'device' => $data['device'] ?? null,
                'location' => $data['location'] ?? null,
                'ip_address' => $data['ip_address'] ?? null,
                'signaling_channel' => $data['signaling_channel'] ?? null,
                'streaming_channel' => $data['streaming_channel'] ?? null,
                'started_at' => date('Y-m-d H:i:s'),
                'heartbeat' => date('Y-m-d H:i:s'),
            ];
            $insert_id = Db::table('streamer')->insertGetId($res);

            $result = $res;
            $result['id'] = $insert_id;

            if ($data['is_testing'] ?? false) {
                Db::rollBack();
                $result['is_testing'] = $data['is_testing'];
            } else {
                Db::commit();
            }
        } catch (\Throwable $th) {
            Db::rollBack();
            return jsonr(["message" => $th->getMessage(), "trace" => $th->getTrace()]);
        }

        // LAST STAGE (Output Process)
        // ===========================
        return json($result);
    }

    public function stop(Request $request)
    {
        // FIRST STAGE (Parameters)
        // ========================
        $data = $request->post();
        try {
            $inputValidator = v::attribute('id', v::intType()->notEmpty());
            $inputValidator->assert((object) $data);
        } catch (NestedValidationException $e) {
            $errAttr = $e->getMessages($this->validatorDesc);
            $errMessage = join(", ", (array) $errAttr['attribute']);
            return jsonr(['message' => $errMessage]);
        }

        // MIDDLE STAGE (Main Process)
        // ===========================
        Db::beginTransaction();
        try {
            $res = [
                'finished_at' => date('Y-m-d H:i:s'),
            ];
            Db::table('streamer')
                ->where(['id' => $data['id'] ?? 0])
                ->update($res);

            if ($data['is_testing'] ?? false) {
                Db::rollBack();
                $result['is_testing'] = $data['is_testing'];
            } else {
                Db::commit();
            }
        } catch (\Throwable $th) {
            Db::rollBack();
            return jsonr(["message" => $th->getMessage(), "trace" => $th->getTrace()]);
        }

        // LAST STAGE (Output Process)
        // ===========================
        $result['message'] = 'done';
        return json($result);
    }

    public function heartbeat(Request $request)
    {
        // FIRST STAGE (Parameters)
        // ========================
        $data = $request->post();

        // MIDDLE STAGE (Main Process)
        // ===========================
        Db::beginTransaction();
        try {
            $res = [
                'heartbeat' => date('Y-m-d H:i:s'),
            ];
            $affected = Db::table('streamer')
                ->where(['id' => $data['id']])
                ->where(['finished_at' => null])
                ->update($res);

            if ($data['is_testing'] ?? false) {
                Db::rollBack();
                $result['is_testing'] = $data['is_testing'];
            } else {
                Db::commit();
            }
        } catch (\Throwable $th) {
            Db::rollBack();
            return jsonr(["message" => $th->getMessage(), "trace" => $th->getTrace()]);
        }

        // LAST STAGE (Output Process)
        // ===========================
        $result['affected'] = $affected;
        return json($result);
    }

    public function join(Request $request)
    {
        // FIRST STAGE (Parameters)
        // ========================
        $data = $request->post();
        try {
            // $inputValidator = v::attribute('id', v::intType()->notEmpty());
            $inputValidator = v::attribute('id', v::notEmpty());
            $inputValidator->assert((object) $data);
        } catch (NestedValidationException $e) {
            $errAttr = $e->getMessages($this->validatorDesc);
            $errMessage = join(", ", (array) $errAttr['attribute']);
            return jsonr(['message' => $errMessage]);
        }

        // MIDDLE STAGE (Main Process)
        // ===========================
        Db::beginTransaction();
        try {
            $res = [
                'views' => Db::raw('views + 1'),
            ];
            Db::table('streamer')->where(['id' => $data['id'] ?? 0])->update($res);

            if ($data['is_testing'] ?? false) {
                Db::rollBack();
                $result['is_testing'] = $data['is_testing'];
            } else {
                Db::commit();
            }
        } catch (\Throwable $th) {
            Db::rollBack();
            return jsonr(["message" => $th->getMessage(), "trace" => $th->getTrace()]);
        }

        // LAST STAGE (Output Process)
        // ===========================
        $result['message'] = 'done';
        return json($result);
    }

    // public function push_send(Request $request)
    // {
    //     // FIRST STAGE (Parameters)
    //     // ========================
    //     $data = $request->post();
    //     try {
    //         $inputValidator = v::attribute('channel_name', v::notEmpty())
    //             ->attribute('event', v::notEmpty())
    //             ->attribute('data', v::notEmpty());
    //         $inputValidator->assert((object) $data);
    //     } catch (NestedValidationException $e) {
    //         $errAttr = $e->getMessages($this->validatorDesc);
    //         $errMessage = join(", ", (array) $errAttr['attribute']);
    //         return jsonr(['message' => $errMessage]);
    //     }

    //     // MIDDLE STAGE (Main Process)
    //     // ===========================
    //     try {
    //         MyFunc::send_notif($data['channel_name'], $data['event'], $data['data']);
    //     } catch (\Throwable $th) {
    //         return jsonr(["message" => $th->getMessage(), "trace" => $th->getTrace()]);
    //     }

    //     // LAST STAGE (Output Process)
    //     // ===========================
    //     $result['message'] = 'done';
    //     return json($result);
    // }


    /**
     * Provide ICE Server Config for WebRTC
     *
     * @param Request   
     * @return  
     */
    public function ice_server_config(Request $request)
    {
        // FIRST STAGE (Parameters)
        // ========================
        $data = $request->post();
        try {
            // $inputValidator = v::attribute('id', v::intType()->notEmpty());
            $inputValidator = v::attribute('server', v::notEmpty());
            $inputValidator->assert((object) $data);
        } catch (NestedValidationException $e) {
            $errAttr = $e->getMessages($this->validatorDesc);
            $errMessage = join(", ", (array) $errAttr['attribute']);
            return jsonr(['message' => $errMessage]);
        }

        // MIDDLE STAGE (Main Process)
        // ===========================
        try {
            switch ($data['server']) {
                case 'rynest':
                    $result = self::rynest_ice_server();
                    break;

                case 'google':
                    $result = self::google_ice_server();
                    break;

                case 'meteredCA':
                    $result = self::google_ice_server();
                    break;

                case 'twilio':
                    $result = self::google_ice_server();
                    break;

                default:
                    $result['message'] = "Unknown config ICE Server [{$data['server']}] !";
                    break;
            }
        } catch (\Throwable $th) {
            return jsonr(["message" => $th->getMessage(), "trace" => $th->getTrace()]);
        }

        // LAST STAGE (Output Process)
        // ===========================
        return json($result);
    }

    private function rynest_ice_server()
    {
        $host = "202.73.24.36:3478";
        $username = "username1";
        $password = "password1";
        $result["ice_servers"] = [
            [
                "url" => "stun:$host",
                "urls" => "stun:$host",
            ],
            [
                "url" => "turn:$host",
                "urls" => "turn:$host",
                "username" => $username,
                "credential" => $password,
            ],
            [
                "url" => "turn:$host?transport=udp",
                "urls" => "turn:$host?transport=udp",
                "username" => $username,
                "credential" => $password,
            ],
            [
                "url" => "turn:$host?transport=tcp",
                "urls" => "turn:$host?transport=tcp",
                "username" => $username,
                "credential" => $password,
            ],
        ];
        $result["iceTransportPolicy"] = "all";
        $result["sdpSemantics"] = "unified-plan";
        return $result;
    }

    private function google_ice_server()
    {
        $result["ice_servers"] = [
            [
                "url" => "stun:stun3.l.google.com:19302",
                "urls" => "stun:stun3.l.google.com:19302",
            ],
        ];
        return $result;
    }
}
