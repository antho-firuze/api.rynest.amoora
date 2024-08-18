<?php

namespace app\api\controller;

use support\Request;
use support\Db;

class Carousel_v1
{
    protected $noNeedLogin = ['index', 'all'];

    public function index(Request $request)
    {
        return json(['message' => "Carousel API v1"]);
    }

    public function all(Request $request)
    {
        try {
            $data = $request->post();

            $carousel = Db::connection('mysql2')->table('sliders')
                ->where('status', '1')
                ->select(
                    'sliders.id',
                    'sliders.url',
                    Db::raw(
                        'CONCAT("https://webapp.amooratravel.com/slider/", sliders.image) as image'
                    ),
                )
                ->get();

            return json($carousel);
        } catch (\Throwable $e) {
            return jsonr(['message' => $e->getMessage()]);
        }
    }
}
