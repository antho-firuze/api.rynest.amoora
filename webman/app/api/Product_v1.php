<?php

namespace app\api;

use support\Request;
use support\Db;
use Firuze\Jwt\JwtToken;
use support\Redis;
use Respect\Validation\Validator as v;
use Respect\Validation\Exceptions\NestedValidationException;

class Product_v1
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

    public function index(Request $request)
    {
        return json(['message' => "Product API v1"]);
    }

    public function all(Request $request)
    {
        // FIRST STAGE (Parameters)
        // ========================
        $data = (object) $request->post();

        // REDIS CHECK STAGE
        // ===================
        try {
            $redisKey = "product-all";
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
            $products = Db::connection('mysql2')->table('products')
                // ->join('product_categories', 'products.product_category_id', '=', 'product_categories.id')
                // ->join('product_images', 'products.id', '=', 'product_images.product_id')
                ->whereRaw('departure_time > ?', [date('Y-m-d H:i:s')])
                ->where('status', 1)
                ->where('deleted_at', null)
                ->select(
                    'products.*',
                    // 'product_categories.name as product_category_name',
                    Db::raw(
                        'CONCAT("https://webapp.amooratravel.com/images/products/", image) as image'
                    ),
                )
                ->orderBy('departure_time')
                ->get();

            // foreach ($products as $key => $value) {
            //     $products[$key]->hotels = $this->get_hotels($value->id);
            //     $products[$key]->airlines = $this->get_airlines($value->id);
            //     $products[$key]->itineraries = $this->get_itineraries($value->id);
            // }

            Db::commit();
        } catch (\Throwable $th) {
            Db::rollBack();
            return jsonr(["message" => $th->getMessage(), "trace" => $th->getTrace()]);
        }

        // LAST STAGE (Output Process)
        // ===========================
        $result = $products;

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
            $redisKey = "product-list";
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
            $products = Db::connection('mysql2')->table('products')
                ->whereRaw('departure_time > ?', [date('Y-m-d H:i:s')])
                ->where('status', 1)
                ->where('deleted_at', null)
                ->select('products.id')
                ->orderBy('departure_time')
                ->get();

            Db::commit();
        } catch (\Throwable $th) {
            Db::rollBack();
            return jsonr(["message" => $th->getMessage(), "trace" => $th->getTrace()]);
        }

        // LAST STAGE (Output Process)
        // ===========================
        $result = $products;

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
            $redisKey = "product-byid-{$data->id}";
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
            $product = Db::connection('mysql2')->table('products')
                // ->join('product_categories', 'products.product_category_id', '=', 'product_categories.id')
                // ->join('product_images', 'products.id', '=', 'product_images.product_id')
                ->where('products.id', '=', $data->id)
                ->select(
                    'products.*',
                    // 'product_categories.name as product_category_name',
                    Db::raw(
                        'CONCAT("https://webapp.amooratravel.com/images/products/", image) as image'
                    ),
                )
                ->first();

            // if ($product) {
            //     $product->hotels = $this->get_hotels($product->id);
            //     $product->airlines = $this->get_airlines($product->id);
            //     $product->itineraries = $this->get_itineraries($product->id);
            // }

            Db::commit();
        } catch (\Throwable $th) {
            Db::rollBack();
            return jsonr(["message" => $th->getMessage(), "trace" => $th->getTrace()]);
        }

        // LAST STAGE (Output Process)
        // ===========================
        $result = $product;

        // Save to Redis
        Redis::set($redisKey, json_encode($result));
        Redis::expire($redisKey, 10);
        return json($result);
    }

    private function get_hotels($product_id)
    {
        $data = Db::connection('mysql2')->table('product_hotels')
            ->join('hotels', 'product_hotels.hotel_id', '=', 'hotels.id')
            ->where('product_id', $product_id)
            ->orderBy('product_hotels.check_in', 'asc')
            ->select(
                'product_hotels.id',
                'product_hotels.hotel_id',
                'product_hotels.check_in',
                'product_hotels.check_out',
                'hotels.name',
                'hotels.rating',
                'hotels.address',
                'hotels.link_map'
            )
            ->get();

        if (!$data) {
            return [];
        }

        return $data;
    }

    private function get_airlines($product_id)
    {
        $data = Db::connection('mysql2')->table('product_airlines')
            ->join('airlines', 'product_airlines.airline_id', '=', 'airlines.id')
            ->where('product_id', $product_id)
            ->orderBy('product_airlines.check_in', 'asc')
            ->select(
                'product_airlines.id',
                'product_airlines.airline_id',
                'product_airlines.check_in',
                'product_airlines.check_out',
                'airlines.name',
                'airlines.code',
                Db::raw('CONCAT("https://webapp.amooratravel.com/", airlines.image) as image'),
            )
            ->get();

        if (!$data) {
            return [];
        }

        return $data;
    }

    private function get_itineraries($product_id)
    {
        $data = Db::connection('mysql2')->table('product_itinereries')
            ->where('product_id', $product_id)
            ->orderBy('activity_date', 'asc')
            ->select(
                'id',
                'title',
                'sub_title',
                'detail_itinerary',
                'activity_date',
            )
            ->get();

        if (!$data) {
            return [];
        }

        return $data;
    }
}
