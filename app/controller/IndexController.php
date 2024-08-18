<?php

namespace app\controller;

use support\Request;

class IndexController
{
    public function index(Request $request)
    {
        return json(["message" => "Welcome to Amoora Travel API !"]);
    }

    // public function home()
    // {
    //     return json([
    //         'server' => $_SERVER,
    //         'env' => getenv(),
    //     ]);
    // }

    // public function view(Request $request)
    // {
    //     return view('index/view', ['name' => 'webman !']);
    // }

    // public function json(Request $request)
    // {
    //     return json(['code' => 0, 'msg' => 'ok']);
    // }

    // public function upload(Request $request)
    // {
    //     return view('upload', ['host' => "http://{$request->host()}"]);
    // }

    // public function upload_file(Request $request)
    // {
    //     $file = $request->file('avatar');
    //     return json($file->getUploadExtension());
    //     if ($file && $file->isValid()) {
    //         $file->move(public_path() . '/files/myfile.' . $file->getUploadExtension());
    //         return json(['code' => 0, 'msg' => 'upload success']);
    //     }
    //     return json(['code' => 1, 'msg' => 'file not found']);
    // }

    // public function test(Request $request)
    // {
    //     return json($request->header());
    // }
}
