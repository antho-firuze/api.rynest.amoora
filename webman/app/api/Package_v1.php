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

    /**
     * Return a simple API version message.
     *
     * @param Request $request
     * @return \support\Response
     */
    public function index(Request $request)
    {
        return json(['message' => "Package API v1"]);
    }

    /**
     * Get all active packages with categories.
     *
     * @param Request $request
     * @return \support\Response
     */
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

        // MIDDLE STAGE (Main Process)
        // ===========================
        Db::beginTransaction();
        try {
            $packages = Db::table('packages')
                ->select('packages.*')
                ->where('is_active', true)
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


    /**
     * Get the list of active package IDs.
     *
     * @param Request $request
     * @return \support\Response
     */
    public function list(Request $request)
    {
        // FIRST STAGE (Parameters)
        // ========================
        // {
        //     "offset": 0,
        //     "limit": 4
        //     "sorts": {
        //         "departure_date": "desc",
        //         "price_quad": "asc"
        //     },
        //     "filter": {
        //         "type": [
        //             "umroh"
        //         ],
        //         "duration_days": [
        //             9,
        //             13,
        //             12
        //         ],
        //         "category_id": [
        //             7,
        //             1,
        //             2
        //         ],
        //         "airline": [
        //             "Lion Air",
        //             "Saudia Airlines",
        //             "Oman Air"
        //         ],
        //         "price_quad": [
        //             30000000,
        //             40600000
        //         ]
        //     }
        // }
        $data = (object) $request->post();

        // REDIS CHECK STAGE
        // ===================
        // try {
        //     $redisKey = "package-list";
        //     $redisVal = Redis::get($redisKey);
        //     if ($redisVal != null) {
        //         $result = json_decode($redisVal);
        //         return json($result);
        //     }
        // } catch (\Throwable $th) {
        //     return jsonr(["message" => $th->getMessage(), "trace" => $th->getTrace()]);
        // }

        // MIDDLE STAGE (Main Process)
        // ===========================
        Db::beginTransaction();
        try {
            $query = Db::table('packages')->where('is_active', true);
            $filterQry = Db::table('packages')->where('is_active', true);

            // FILTER INPUT
            $filterFields = ['type', 'duration_days', 'category_id', 'airline'];
            $filterMinMax = ['price_quad'];
            if (isset($data->filter)) {
                foreach ($data->filter as $key => $value) {
                    if (in_array($key, $filterFields)) {
                        $query->whereIn($key, $value);
                        $filterQry = $filterQry->whereIn($key, $value);
                    }
                    if (in_array($key, $filterMinMax)) {
                        $query->when($data->filter[$key], function ($query, array $price) use ($key) {
                            return $query->where($key, '>=', $price[0])->where($key, '<=', $price[1]);
                        });
                        $filterQry->when($data->filter[$key], function ($query, array $price) use ($key) {
                            return $query->where($key, '>=', $price[0])->where($key, '<=', $price[1]);
                        });
                    }
                }
            }

            // SORTING
            if (isset($data->sorts)) {
                foreach ($data->sorts as $column => $direction) {
                    $query->orderBy($column, $direction);
                }
            }

            // COUNT ALL RECORDS
            // Places before offset & limit 
            // otherwise became not accurate when offset not zero (0)
            $count = $query->count();

            // OFFSET & LIMIT
            $offset = 0;
            $limit = 5;
            if (isset($data->offset)) {
                $offset = is_int($data->offset) ? $data->offset : $offset;
            }
            if (isset($data->limit)) {
                $limit = is_int($data->limit) ? $data->limit : $limit;
            }
            $query->offset($offset)->limit($limit);

            // CREATE FILTER FIELDS FOR OUTPUT
            $filter = [];
            foreach ($filterFields as $key => $value) {
                $filter[$value] = $filterQry->distinct()->pluck($value);
            }

            // RESULT
            // return json($query->toRawSql());
            $packages = $query->select('packages.id')->get();

            Db::commit();
        } catch (\Throwable $th) {
            Db::rollBack();
            return jsonr(["message" => $th->getMessage(), "trace" => $th->getTrace()]);
        }

        // LAST STAGE (Output Process)
        // ===========================
        $result['offset'] = $offset;
        $result['limit'] = $limit;
        $result['filter'] = $filter;
        $result['count'] = $count;
        $result['result'] = $packages;

        // Save to Redis
        // Redis::set($redisKey, json_encode($result));
        // Redis::expire($redisKey, 10);
        return json($result);
    }

    /**
     * Get package details by ID including image URL and categories.
     *
     * @param Request $request
     * @return \support\Response
     */
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

    /**
     * Fetch categories for the given package category ID.
     *
     * @param int $id
     * @return array
     */
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
