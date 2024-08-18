<?php

namespace app\api\controller;

use support\Request;
use support\Db;
use Firuze\Jwt\JwtToken;

class Notification_v1
{
    protected $noNeedLogin = ['index'];

    public function index(Request $request)
    {
        return json(['message' => "This is Notification API v1"]);
    }

    public function create(Request $request)
    {
        try {
            $user_id = JwtToken::getCurrentId();
            $data = $request->post();

            // Default Value
            $fields = [
                'user_id' => $user_id,
                'created_at' => date('Y-m-d H:i:s'),
                'is_read' => false,
                'pinned' => false,
                'pinned_duration' => 86400,
            ];

            $table_fields = ['title', 'body', 'image', 'topic', 'is_read', 'pinned', 'pinned_duration', 'created_at'];
            foreach ($data as $key => $value) {
                if (in_array($key, $table_fields)) {
                    $fields[$key] = $value;
                }
            }
            $insert_id = Db::table('notification')->insertGetId($fields);

            $fields['id'] = $insert_id;

            return json($fields);
        } catch (\Throwable $e) {
            return jsonr(['message' => $e->getMessage()]);
        }
    }

    public function update(Request $request)
    {
        try {
            $user_id = JwtToken::getCurrentId();
            $data = $request->post();

            $table_fields = ['is_read', 'pinned', 'pinned_duration'];
            foreach ($data as $key => $value) {
                if (in_array($key, $table_fields)) {
                    $fields[$key] = $value;
                }
            }
            Db::table('notification')->where(['id' => $data['id']])->update($fields);

            return json($fields);
        } catch (\Throwable $e) {
            return jsonr(['message' => $e->getMessage()]);
        }
    }

    public function delete(Request $request)
    {
        try {
            $user_id = JwtToken::getCurrentId();
            $data = $request->post();

            Db::table('notification')->where('id', '=', $data['id'])->delete();

            return json(['message' => 'done']);
        } catch (\Throwable $e) {
            return jsonr(['message' => $e->getMessage()]);
        }
    }
}
