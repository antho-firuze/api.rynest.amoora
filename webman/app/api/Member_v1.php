<?php

namespace app\api;

use support\Request;
use support\Redis;
use support\Db;
use Firuze\Jwt\JwtToken;
use support\MyFunc;
use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\NestedValidationException;

class Member_v1
{
    public $ok = 'done';
    /**
     * Methods that do not require login
     */
    protected $noNeedLogin = ['index'];

    public function index(Request $request)
    {
        return json(['message' => "This is User API v1 !"]);
    }

    public function profile(Request $request)
    {
        // FIRST STAGE (Parameters)
        // ========================
        $data = (object) $request->post();
        $user_id = $data->user_id ?? JwtToken::getCurrentId();

        // MIDDLE STAGE (Main Process)
        // ===========================
        Db::beginTransaction();
        try {
            $user = Db::table('users')
                ->where('id', $user_id)
                ->first();
            $member = Db::table('members')
                ->where('user_id', $user_id)
                ->first();

            Db::commit();
        } catch (\Throwable $th) {
            Db::rollBack();
            return jsonr(["message" => $th->getMessage(), "trace" => $th->getTrace()]);
        }

        // LAST STAGE (Output Process)
        // ===========================
        $result = [
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
        ];
        return json($result);
    }

    public function update_profile(Request $request)
    {
        // FIRST STAGE (Parameters)
        // ========================
        $data = (object) $request->post();
        $key = array_key_first((array) $data);
        $table_user = ['name', 'email'];
        $table_member = ['full_name', 'phone', 'address', 'passport_no'];
        if (!in_array($key, $table_user) && !in_array($key, $table_member)) {
            $result['message'] = "[{$key}] field not recognized !";
            return jsonr($result);
        }

        Db::beginTransaction();
        try {
            $user_id = JwtToken::getCurrentId();

            if (in_array($key, $table_user)) {
                Db::table('users')
                    ->where('id', $user_id)
                    ->update([$key => $data->{$key}]);

                $result[$key] = $data->{$key};
            }
            if (in_array($key, $table_member)) {
                Db::table('members')
                    ->where('user_id', $user_id)
                    ->update([$key =>  $data->{$key}]);

                $result[$key] = $data->{$key};
            }

            if ($data->is_testing ?? false) {
                Db::rollBack();
                $result['is_testing'] = $data->is_testing;
            } else {
                Db::commit();
            }
        } catch (\Throwable $th) {
            Db::rollBack();
            return jsonr(["message" => $th->getMessage(), "trace" => $th->getTrace()]);
        }

        // LAST STAGE (Output Process)
        // ===========================
        $result['message'] = $this->ok;
        return json($result);
    }

    public function upload_photo(Request $request)
    {
        // FIRST STAGE (Parameters)
        // ========================
        $data = (object) $request->post();

        // MIDDLE STAGE (Main Process)
        // ===========================
        Db::beginTransaction();
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
            $config['upload_path'] = 'members/';
            $config['allowed_types'] = ['jpg', 'jpeg', 'png', 'bmp', 'gif'];
            $config['max_size'] = 1000;     // in KB
            $url = MyFunc::upload_file($request, $config);

            // update table members
            Db::table('members')
                ->where('id', $member->id)
                ->update(['photo' => $url]);

            $result = ['url' => $url];

            Db::commit();
        } catch (\Throwable $th) {
            Db::rollBack();
            return jsonr(["message" => $th->getMessage(), "trace" => $th->getTrace()]);
        }

        // LAST STAGE (Output Process)
        // ===========================
        return json($result);
    }

    // public function sample(Request $request)
    // {
    //     Db::table('users')->where('votes', '>', 100)->delete();
    //     return json(['message' => "broadcast_listener API"]);
    // }
}
