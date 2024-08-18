<?php

namespace app\api\controller;

use support\Request;
use support\Db;
use Firuze\Jwt\JwtToken;
use support\Response;

class Broadcast_v1
{
    protected $noNeedLogin = ['index'];

    public function index(Request $request)
    {
        // $res = ['message' => "This is Broadcast API v1 !"];
        // return json($res);
        try {
            // $obsolete = date('Y-m-d H:i:s');
            $obsolete = date('Y-m-d H:i:s', strtotime('+10 second'));
            Db::table('presenter')->where('heartbeat', '<', $obsolete)->delete();
            return json($obsolete);
        } catch (\Throwable $e) {
            return jsonr(['message' => $e->getMessage()]);
        }
    }

    public function check_existing_live(Request $request)
    {
        try {
            $user_id = JwtToken::getCurrentId();
            $data = $request->post();

            $presenter = Db::table('presenter')->where('user_id', $user_id)->first();
            if ($presenter) {
                $presenter->profile = $this->get_profile($user_id);
                return json($presenter);
            }

            return jsonr(['message' => 'not exists live session']);
        } catch (\Throwable $e) {
            return jsonr(['message' => $e->getMessage()]);
        }
    }

    public function start(Request $request)
    {
        try {
            $user_id = JwtToken::getCurrentId();
            $data = $request->post();

            $presenter = Db::table('presenter')->where('user_id', $user_id)->first();
            if ($presenter) {
                $presenter->profile = $this->get_profile($user_id);
                return json($presenter);
            }

            $res = [
                'user_id' => $user_id,
                'label' => $data['label'],
                'session' => $data['session'],
                'channel' => $data['channel'] ?? null,
                'ip_address' => $data['ip_address'],
                'ip_broadcast' => $data['ip_broadcast'],
                'port' => $data['port'],
                'created_at' => date('Y-m-d H:i:s'),
                'heartbeat' => date('Y-m-d H:i:s'),
            ];
            $insert_id = Db::table('presenter')->insertGetId($res);

            $res['id'] = $insert_id;
            $res['profile'] = $this->get_profile($user_id);

            return json($res);
        } catch (\Throwable $e) {
            return jsonr(['message' => $e->getMessage()]);
        }
    }

    public function stop(Request $request)
    {
        try {
            $user_id = JwtToken::getCurrentId();
            $data = $request->post();

            Db::table('presenter')->where('id', '=', $data['id'])->delete();

            return json(['message' => 'done']);
        } catch (\Throwable $e) {
            return jsonr(['message' => $e->getMessage()]);
        }
    }

    public function presenter_heartbeat(Request $request)
    {
        try {
            $user_id = JwtToken::getCurrentId();
            $data = $request->post();

            $res = [
                'heartbeat' => date('Y-m-d H:i:s'),
            ];
            Db::table('presenter')->where(['id' => $data['id']])->update($res);

            return json($res);
        } catch (\Throwable $e) {
            return jsonr(['message' => $e->getMessage()]);
        }
    }

    // public function online_audience(Request $request)
    // {
    //     try {
    //         $user_id = JwtToken::getCurrentId();
    //         $data = $request->post();

    //         $audience = Db::table('audience')
    //         ->where('presenter_id', $data['presenter_id'])
    //         ->get();

    //         foreach ($audience as $key => $value) {
    //             $audience[$key]->profile = $this->get_profile($value->user_id);
    //         }

    //         return json($audience);
    //     } catch (\Throwable $e) {
    //         return jsonr(['message' => $e->getMessage()]);
    //     }
    // }

    // public function online_host(Request $request)
    // {
    //     try {
    //         $user_id = JwtToken::getCurrentId();
    //         $data = $request->post();

    //         $presenters = Db::table('presenter')
    //             // ->where('channel', $data['channel'])
    //             ->get();

    //         foreach ($presenters as $key => $value) {
    //             $presenters[$key]->profile = $this->get_profile($value->user_id);
    //         }

    //         return json($presenters);
    //     } catch (\Throwable $e) {
    //         return jsonr(['message' => $e->getMessage()]);
    //     }
    // }

    public function join_channel(Request $request)
    {
        try {
            $user_id = JwtToken::getCurrentId();
            $data = $request->post();

            Db::table('audience')->where('user_id', '=', $user_id)->delete();

            $res = [
                'user_id' => $user_id,
                'presenter_id' => $data['presenter_id'],
                'session' => $data['session'],
                'created_at' => date('Y-m-d H:i:s'),
                'heartbeat' => date('Y-m-d H:i:s'),
            ];
            $insert_id = Db::table('audience')->insertGetId($res);

            $res['id'] = $insert_id;

            return json($res);
        } catch (\Throwable $e) {
            return jsonr(['message' => $e->getMessage()]);
        }
    }

    public function leave_channel(Request $request)
    {
        try {
            $user_id = JwtToken::getCurrentId();
            $data = $request->post();

            Db::table('audience')->where('id', '=', $data['id'])->delete();

            return json(['message' => 'done']);
        } catch (\Throwable $e) {
            return jsonr(['message' => $e->getMessage()]);
        }
    }

    public function audience_heartbeat(Request $request)
    {
        try {
            $user_id = JwtToken::getCurrentId();
            $data = $request->post();

            $res = [
                'heartbeat' => date('Y-m-d H:i:s'),
            ];
            Db::table('audience')->where(['id' => $data['id']])->update($res);

            return json($res);
        } catch (\Throwable $e) {
            return jsonr(['message' => $e->getMessage()]);
        }
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
