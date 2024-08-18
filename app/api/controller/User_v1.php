<?php

namespace app\api\controller;

use support\Request;
use support\Redis;
use support\Db;
use Firuze\Jwt\JwtToken;
use support\MyFunc;

class User_v1
{
    public $ok = 'done';
    /**
     * Methods that do not require login
     */
    protected $noNeedLogin = ['index'];

    public function index(Request $request)
    {
        return json([
            'message' => "This is User API v1 !",
        ]);
    }

    public function profile(Request $request)
    {
        $user_id = JwtToken::getCurrentId();
        $data = $request->post();

        $user_id = $data['user_id'] ?? $user_id;

        $user = Db::table('users')
            ->where('id', $user_id)
            ->first();
        $member = Db::table('members')
            ->where('user_id', $user_id)
            ->first();

        return json([
            'user_id' => $user->id,
            'member_id' => $member->id,
            'identifier' => $user->identifier,
            'name' => $user->name,
            'email' => $user->email,
            'is_email_verified' => $user->is_email_verified,
            'full_name' => $member->full_name,
            'phone' => $member->phone,
            'is_phone_verified' => $member->is_phone_verified,
            'address' => $member->address,
            'photo' => $member->photo,
            'passport_no' => $member->passport_no,
        ]);
    }

    public function update_profile(Request $request)
    {
        try {
            $user_id = JwtToken::getCurrentId();
            $data = $request->post();

            $table_user = ['name', 'email'];
            $table_member = ['full_name', 'phone', 'address', 'passport_no'];

            $key = array_key_first($data);

            if (in_array($key, $table_user)) {
                Db::table('users')
                    ->where('id', $user_id)
                    ->update([$key => $data[$key]]);

                return json($data);
            }
            if (in_array($key, $table_member)) {
                Db::table('members')
                    ->where('user_id', $user_id)
                    ->update([$key => $data[$key]]);

                return json($data);
            }

            $result['message'] = 'Unknown field !';
            return jsonr($result);
        } catch (\Throwable $e) {
            return jsonr(['message' => $e->getMessage()]);
        }
    }

    public function upload_photo(Request $request)
    {
        try {
            $user_id = JwtToken::getCurrentId();
            $member = Db::table('members')
                ->where('user_id', $user_id)
                ->first();

            // remove old photo
            if (!empty($member->photo)) {
                @unlink(public_path(path: $member->photo));
            }

            // upload new photo
            $config['userfile'] = 'avatar';
            $config['file_name'] = "avatar-{$member->id}";
            $config['upload_path'] = '/members/';
            $config['allowed_types'] = ['jpg', 'jpeg', 'png', 'bmp', 'gif'];
            $config['max_size'] = 500;     // in KB
            $result = MyFunc::upload_file($request, $config);

            // update table members
            Db::table('members')
                ->where('id', $member->id)
                ->update(['photo' => $result['path']]);

            return json($result);
        } catch (\Throwable $th) {
            $error['message'] = $th->getMessage();
            switch ($th->getCode()) {
                case 1:
                    $error['allowed_types'] = join('|', $config['allowed_types']);
                    return jsonr($error);
                case 2:
                    $error['max_size'] = $config['max_size'] . "KB";
                    return jsonr($error);

                default:
                    return jsonr($error);
            }
        }
    }

    // public function sample(Request $request)
    // {
    //     Db::table('users')->where('votes', '>', 100)->delete();
    //     return json(['message' => "broadcast_listener API"]);
    // }
}
