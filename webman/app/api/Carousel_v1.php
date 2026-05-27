<?php

namespace app\api;

use support\Request;
use support\Db;
use support\Redis;
use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\NestedValidationException;

class Carousel_v1
{
    protected $noNeedLogin = ['index', 'all'];

    protected $endPointCDN = 'https://webapp.amooratravel.com';

    public function index(Request $request)
    {
        return json(['message' => "Carousel API v1"]);
    }

    public function all(Request $request)
    {
        // FIRST STAGE (Parameters)
        // ========================
        $data = (object) $request->post();

        // REDIS CHECK STAGE
        // ===================
        try {
            $redisKey = "carousel-all";
            $redisVal = Redis::get($redisKey);
            if ($redisVal != null) {
                $result = json_decode($redisVal);
                return json($result);
            }
        } catch (\Throwable $th) {
            return jsonr(["message" => $th->getMessage(), "trace" => $th->getTrace()]);
        }

        // MIDDLE STAGE (Main Process)
        // ===========================
        Db::beginTransaction();
        try {
            $carousel = Db::connection('mysql2')->table('sliders')
                ->where('status', '1')
                ->select(
                    'sliders.id',
                    // 'sliders.url',
                    Db::raw(
                        "CONCAT('{$this->endPointCDN}', sliders.image) as image"
                    ),
                )
                ->get();

            Db::commit();
        } catch (\Throwable $th) {
            Db::rollBack();
            return jsonr(["message" => $th->getMessage(), "trace" => $th->getTrace()]);
        }

        // LAST STAGE (Output Process)
        // ===========================
        $result = $carousel;

        // Save to Redis
        Redis::set($redisKey, json_encode($result));
        Redis::expire($redisKey, 10);
        return json($result);
    }
}
