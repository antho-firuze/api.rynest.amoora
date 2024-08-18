<?php

namespace app\api\controller;

use support\Request;
use support\Db;
use Firuze\Jwt\JwtToken;

class Product_v1
{
    protected $noNeedLogin = ['index', 'all', 'list', 'byId'];

    public function index(Request $request)
    {
        return json(['message' => "Product API v1"]);
    }

    public function all(Request $request)
    {
        try {
            $data = $request->post();

            $products = Db::connection('mysql2')->table('products')
                ->join('product_categories', 'products.product_category_id', '=', 'product_categories.id')
                ->join('product_images', 'products.id', '=', 'product_images.product_id')
                ->where('status', 1)
                ->select(
                    'products.*',
                    'product_categories.name as product_category_name',
                    Db::raw(
                        'CONCAT("https://webapp.amooratravel.com/images/products/", product_images.image) as image'
                    ),
                )
                ->get();

            foreach ($products as $key => $value) {
                $products[$key]->hotels = $this->get_hotels($value->id);
                $products[$key]->airlines = $this->get_airlines($value->id);
                $products[$key]->itineraries = $this->get_itineraries($value->id);
            }

            return json($products);
        } catch (\Throwable $e) {
            return jsonr(['message' => $e->getMessage()]);
        }
    }

    public function list(Request $request)
    {
        try {
            $data = $request->post();

            $products = Db::connection('mysql2')->table('products')
                ->where('status', 1)
                ->select('products.id')
                ->get();

            return json($products);
        } catch (\Throwable $e) {
            return jsonr(['message' => $e->getMessage()]);
        }
    }

    public function byId(Request $request)
    {
        try {
            $data = $request->post();
            if (!$data['id']) {
                return json((object)[]);
            }

            $product = Db::connection('mysql2')->table('products')
                ->join('product_categories', 'products.product_category_id', '=', 'product_categories.id')
                ->join('product_images', 'products.id', '=', 'product_images.product_id')
                ->where('products.id', '=', $data['id'])
                ->select(
                    'products.*',
                    'product_categories.name as product_category_name',
                    Db::raw(
                        'CONCAT("https://webapp.amooratravel.com/images/products/", product_images.image) as image'
                    ),
                )
                ->first();

            if ($product) {
                $product->hotels = $this->get_hotels($product->id);
                $product->airlines = $this->get_airlines($product->id);
                $product->itineraries = $this->get_itineraries($product->id);
            }

            return json($product ?? (object)[]);
        } catch (\Throwable $e) {
            return jsonr(['message' => $e->getMessage()]);
        }
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
