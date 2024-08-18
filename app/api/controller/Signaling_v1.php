<?php

namespace app\api\controller;

use support\Request;
use support\Db;
use Firuze\Jwt\JwtToken;
use support\Response;

class Signaling_v1
{
    protected $noNeedLogin = ['index'];

    public function index(Request $request)
    {
        $res = ['message' => "This is Signaling API v1 !"];
        return json($res);
    }

    // PRESENTER SECTION
    public function createPresenter(Request $request)
    {
        try {
            $user_id = JwtToken::getCurrentId();
            $data = $request->post();

            // Remove existing presenter
            Db::table('presenter')->where('user_id', $user_id)->delete();

            // Default Value
            $fields = [
                'user_id' => $user_id,
                'state' => 'active',
                'created_at' => date('Y-m-d H:i:s'),
                'heartbeat' => date('Y-m-d H:i:s'),
            ];

            $table_fields = ['label', 'heartbeat', 'state'];
            foreach ($data as $key => $value) {
                if (in_array($key, $table_fields)) {
                    $fields[$key] = $value;
                }
            }
            $insert_id = Db::table('presenter')->insertGetId($fields);

            $fields['id'] = $insert_id;
            $fields['profile'] = $this->get_profile($user_id);

            return json($fields);
        } catch (\Throwable $e) {
            return jsonr(['message' => $e->getMessage()]);
        }
    }

    public function updatePresenter(Request $request)
    {
        try {
            $user_id = JwtToken::getCurrentId();
            $data = $request->post();

            // Default Value
            $fields = [
                'heartbeat' => date('Y-m-d H:i:s'),
            ];

            // Enumerate Updated Fields
            $table_fields = ['label', 'heartbeat', 'state'];
            foreach ($data as $key => $value) {
                if (in_array($key, $table_fields)) {
                    $fields[$key] = $value;
                }
            }
            Db::table('presenter')->where(['id' => $data['id']])->update($fields);

            return json($fields);
        } catch (\Throwable $e) {
            return jsonr(['message' => $e->getMessage()]);
        }
    }

    public function removePresenter(Request $request)
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

    // AUDIENCE SECTION
    public function createAudience(Request $request)
    {
        try {
            $user_id = JwtToken::getCurrentId();
            $data = $request->post();

            // Remove existing audience
            Db::table('audience')->where('user_id', '=', $user_id)->delete();

            // Default Value
            $fields = [
                'user_id' => $user_id,
                'offer' => null,
                'answer' => null,
                'state' => 'join',
                'created_at' => date('Y-m-d H:i:s'),
                'heartbeat' => date('Y-m-d H:i:s'),
            ];

            $table_fields = ['presenter_id', 'offer', 'answer', 'heartbeat', 'state'];
            foreach ($data as $key => $value) {
                if (in_array($key, $table_fields)) {
                    $fields[$key] = $value;
                }
            }
            $insert_id = Db::table('audience')->insertGetId($fields);

            $fields['id'] = $insert_id;
            $fields['profile'] = $this->get_profile($user_id);

            return json($fields);
        } catch (\Throwable $e) {
            return jsonr(['message' => $e->getMessage()]);
        }
    }

    public function updateAudience(Request $request)
    {
        try {
            $user_id = JwtToken::getCurrentId();
            $data = $request->post();

            // Default Value
            $data['heartbeat'] = isset($data['heartbeat']) ? $data['heartbeat'] : date('Y-m-d H:i:s');

            // Enumerate Updated Fields
            $table_fields = ['offer', 'answer', 'heartbeat', 'state'];
            foreach ($data as $key => $value) {
                if (in_array($key, $table_fields)) {
                    $fields[$key] = $value;
                }
            }

            Db::table('audience')->where(['id' => $data['id']])->update($fields);

            return json($fields);
        } catch (\Throwable $e) {
            return jsonr(['message' => $e->getMessage()]);
        }
    }

    public function removeAudience(Request $request)
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

    public function removeAudienceByPresenterId(Request $request)
    {
        try {
            $user_id = JwtToken::getCurrentId();
            $data = $request->post();

            Db::table('audience')->where('presenter_id', '=', $data['presenter_id'])->delete();

            return json(['message' => 'done']);
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
