<?php

namespace app\api;

use support\Request;
use support\Db;
use Firuze\Jwt\JwtToken;
use support\Redis;
use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\NestedValidationException;

class Package_v1
{
    protected $noNeedLogin = ['index', 'all', 'list', 'byId'];

    protected $validatorDesc = [
        'attribute' => 'Params [{{name}}] is required',
        'stringType' => '[{{name}}] must be a string type',
        'intType' => '[{{name}}] must be integer',
        'email' => '[{{name}}] must be a valid email',
        'boolType' => '[{{name}}] must be a boolean type',
        'notEmpty' => '[{{name}}] must not empty',
    ];

    protected $endPointCDN = 'https://webapp.amooratravel.com';

    public function index(Request $request)
    {
        return json(['message' => "Package API v1"]);
    }

    public function all(Request $request)
    {
        // FIRST STAGE (Parameters)
        // ========================
        $data = (object) $request->post();

        // REDIS CHECK STAGE
        // ===================
        try {
            $redisKey = "package-all";
            $redisVal = Redis::get($redisKey);
            if ($redisVal != null) {
                $result = json_decode($redisVal);
                return json($result);
            }
        } catch (\Throwable $th) {
            return jsonr(["message" => $th->getMessage(), "trace" => $th->getTrace()]);
        }

        // return jsonr(getenv('DB_HOST3'));

        // MIDDLE STAGE (Main Process)
        // ===========================
        Db::beginTransaction();
        try {
            $packages = Db::table('packages')
                ->where('is_active', true)
                // ->select('packages.*')
                ->select(
                    'packages.*',
                    Db::raw(
                        "CONCAT('{$this->endPointCDN}', image_path) as image"
                    ),
                )
                ->orderBy('sort_order')
                ->get();

            foreach ($packages as $key => $value) {
                $packages[$key]->categories = $this->get_categories($value->category_id);
            }

            Db::commit();
        } catch (\Throwable $th) {
            Db::rollBack();
            return jsonr(["message" => $th->getMessage(), "trace" => $th->getTrace()]);
        }

        // LAST STAGE (Output Process)
        // ===========================
        $result = $packages;

        // Save to Redis
        Redis::set($redisKey, json_encode($result));
        Redis::expire($redisKey, 10);
        return json($result);
    }

    public function list(Request $request)
    {
        // FIRST STAGE (Parameters)
        // ========================
        $data = (object) $request->post();

        // REDIS CHECK STAGE
        // ===================
        try {
            $redisKey = "package-list";
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
            $packages = Db::table('packages')
                // ->whereRaw('departure_time > ?', [date('Y-m-d H:i:s')])
                ->where('is_active', true)
                ->select('packages.id')
                ->orderBy('sort_order')
                ->get();

            Db::commit();
        } catch (\Throwable $th) {
            Db::rollBack();
            return jsonr(["message" => $th->getMessage(), "trace" => $th->getTrace()]);
        }

        // LAST STAGE (Output Process)
        // ===========================
        $result = $packages;

        // Save to Redis
        Redis::set($redisKey, json_encode($result));
        Redis::expire($redisKey, 10);
        return json($result);
    }

    public function byId(Request $request)
    {
        // FIRST STAGE (Parameters)
        // ========================
        $data = (object) $request->post();
        try {
            $inputValidator = v::attribute('id', v::intType()->notEmpty());
            $inputValidator->assert($data);
        } catch (NestedValidationException $e) {
            $errAttr = $e->getMessages($this->validatorDesc);
            $errMessage = join(", ", (array) $errAttr['attribute']);
            return jsonr(['message' => $errMessage]);
        }

        // REDIS CHECK STAGE
        // ===================
        try {
            $redisKey = "package-byid-{$data->id}";
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
            $package = Db::connection('pgsql')->table('packages')
                ->where('packages.id', '=', $data->id)
                ->select(
                    'packages.*',
                    Db::raw(
                    "CONCAT('{$this->endPointCDN}', image_path) as image"
                    ),
                )
                ->first();

            if ($package) {
                $package->categories = $this->get_categories($package->category_id);
            }

            Db::commit();
        } catch (\Throwable $th) {
            Db::rollBack();
            return jsonr(["message" => $th->getMessage(), "trace" => $th->getTrace()]);
        }

        // LAST STAGE (Output Process)
        // ===========================
        $result = $package;

        // Save to Redis
        Redis::set($redisKey, json_encode($result));
        Redis::expire($redisKey, 10);
        return json($result);
    }

    private function get_categories(int $id)
    {
        $data = Db::table('package_categories')
            ->where('id', $id)
            ->select('*')
            ->get();

        if (!$data) {
            return [];
        }

        return $data;
    }

}
