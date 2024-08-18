<?php

namespace app\api\controller;

use support\Request;
use support\Db;
use Firuze\Jwt\JwtToken;

class Maps_v1
{
    protected $noNeedLogin = ['index'];

    public function index(Request $request)
    {
        return json(['message' => "maps_live_location API"]);
    }

    public function save_location(Request $request)
    {
        try {
            $user_id = JwtToken::getCurrentId();
            $data = $request->post();

            $log = [
                'user_id' => $user_id,
                'lat' => $data['lat'],
                'lng' => $data['lng'],
                'time' => $data['heartbeat'] ?? date('Y-m-d H:i:s'),
            ];
            Db::table('log_location')->insert($log);

            $live = [
                'user_id' => $user_id,
                'label' => $data['label'],
                'lat' => $data['lat'],
                'lng' => $data['lng'],
                'heartbeat' => $data['heartbeat'] ?? date('Y-m-d H:i:s'),
            ];
            Db::table('live_location')->updateOrInsert(['user_id' => $user_id], $live);

            return json($live);
        } catch (\Throwable $e) {
            return jsonr(['message' => $e->getMessage()]);
        }
    }

    public function log(Request $request)
    {
        try {
            $user_id = JwtToken::getCurrentId();
            $data = $request->post();

            $user_id = $data['user_id'] ?? $user_id;

            $log_location = Db::table('log_location')
                ->where(['user_id' => $user_id])
                ->whereRaw("DATE_FORMAT(time, '%Y-%m-%d') = ?", $data['time'])
                ->get();

            return json($log_location);
        } catch (\Throwable $e) {
            return jsonr(['message' => $e->getMessage()]);
        }
    }
}
